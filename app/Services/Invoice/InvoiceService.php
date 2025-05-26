<?php

namespace App\Services\Invoice;

use App\Models\Payment;
use App\Settings\InfoSettings;
use Brick\Money\Money;
use Elegantly\Invoices\Pdf\PdfInvoice;
use Elegantly\Invoices\Pdf\PdfInvoiceItem;
use Elegantly\Invoices\Support\Address;
use Elegantly\Invoices\Support\Buyer;
use Elegantly\Invoices\Support\Seller;
use Exception;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use URL;

class InvoiceService
{
    public function __construct(protected InfoSettings $infoSettings) {}

    /**
     * Generate a secure token for invoice access
     */
    public function generateInvoiceToken(Payment $payment): string
    {
        $route = URL::temporarySignedRoute(
            "payment.invoice", // route name
            now()->addMonth(), // expiration
            ["payment" => $payment->id]
        );
        return getQuery($route);
    }

    /**
     * Download invoice as PDF
     * @throws Exception
     */
    public function downloadInvoice(Payment $payment): \Illuminate\Http\Response
    {
        $invoice = $this->generateInvoice($payment);

        return $invoice->download($this->generateFilename($payment));
    }

    /**
     * Generate PDF invoice for a payment
     * @throws Exception
     */
    public function generateInvoice(Payment $payment): PdfInvoice
    {
        $payable = $payment->payable;
        $customer = $payable->getCustomer();

        return new PdfInvoice(
            state: "paid",
            serial_number: $this->generateSerialNumber($payment),
            created_at: $payment->created_at,
            paid_at: $payment->paid_at,
            seller: $this->createSeller(),
            buyer: $this->createBuyer($customer),
            items: [$this->createInvoiceItem($payable, $payment)],
            description: $this->generateDescription($payable),
            template: "default.layout",
            templateData: [
                "color" => "#050038",
            ],
            logo: $this->getLogo()
        );
    }

    /**
     * Generate serial number for the invoice
     */
    protected function generateSerialNumber(Payment $payment): string
    {
        $payable = $payment->payable;
        $modelType = class_basename($payable);

        $prefix = match ($modelType) {
            "Booking" => "BOK",
            "Rent" => "RNT",
            "Shipping" => "SHP",
            default => "INV",
        };

        $year = now()->format("y");
        $sequence = str_pad($payment->id, 4, "0", STR_PAD_LEFT);

        return "$prefix$year$sequence";
    }

    /**
     * Create seller information
     */
    protected function createSeller(): Seller
    {
        $locale = app()->getLocale();

        return new Seller(
            company: $this->infoSettings->name[$locale] ?? config("app.name"),
            address: new Address(
                street: $this->infoSettings->address[$locale] ?? "",
                postal_code: "",
                city: "",
                country: ""
            ),
            email: $this->getFirstEmail(),
            phone: $this->getFirstPhone() // Add tax number if available
        );
    }

    /**
     * Get first email from settings
     */
    protected function getFirstEmail(): ?string
    {
        if (!empty($this->infoSettings->emails)) {
            return $this->infoSettings->emails[0]["email"] ?? null;
        }
        return null;
    }

    /**
     * Get first phone from settings
     */
    protected function getFirstPhone(): ?string
    {
        if (!empty($this->infoSettings->phones)) {
            return $this->infoSettings->phones[0]["number"] ?? null;
        }
        return null;
    }

    /**
     * Create buyer information
     */
    protected function createBuyer($customer): Buyer
    {
        return new Buyer(
            name: $customer->name,
            email: $customer->email,
            phone: $customer->phone_number
        );
    }

    /**
     * Create invoice item from payable
     * @throws Exception
     */
    protected function createInvoiceItem(
        $payable,
        Payment $payment
    ): PdfInvoiceItem {
        // Convert from minor units (cents) to major units for Money object
        return new PdfInvoiceItem(
            label: $this->getItemLabel($payable),
            unit_price: Money::of(
                $payment->amount / 100,
                $payment->currency_code
            ),
            tax_percentage: 0, // Add tax if applicable
            quantity: 1,
            description: $this->generateItemDescription($payable)
        );
    }

    /**
     * Get item label based on payable type
     */
    protected function getItemLabel($payable): string
    {
        $modelType = class_basename($payable);

        return match ($modelType) {
            "Booking" => "Car Booking Service",
            "Rent" => "Car Rental Service",
            "Shipping" => "Shipping Service",
            default => $modelType . " Service",
        };
    }

    /**
     * Generate item description based on payable type
     */
    protected function generateItemDescription($payable): string
    {
        $modelType = class_basename($payable);
        $reference = $payable->reference_number ?? $payable->id;

        switch ($modelType) {
            case "Booking":
                $vehicle = $payable->vehicle;
                $description = "Vehicle: $vehicle->name ($vehicle->license_plate)";
                $description .=
                    "\nFrom: " . $payable->start_datetime->format("Y-m-d H:i");
                $description .=
                    "\nTo: " . $payable->end_datetime->format("Y-m-d H:i");
                $description .= "\nPickup: " . $payable->pickup_address;
                if ($payable->destination_address) {
                    $description .=
                        "\nDestination: " . $payable->destination_address;
                }
                break;

            case "Rent":
                $vehicle = $payable->vehicle;
                $description = "Vehicle: $vehicle->name ($vehicle->license_plate)";
                $description .=
                    "\nRental Period: " .
                    $payable->rental_start_date->format("Y-m-d H:i") .
                    " to " .
                    $payable->rental_end_date->format("Y-m-d H:i");
                $description .= "\nPickup: " . $payable->pickup_address;
                $description .= "\nDrop-off: " . $payable->drop_off_address;
                break;

            case "Shipping":
                $description = "From: " . $payable->pickup_address;
                $description .= "\nTo: " . $payable->delivery_address;
                $description .=
                    "\nTotal Weight: " . $payable->total_weight . " kg";
                $description .= "\nItems: " . $payable->items()->count();
                break;

            default:
                $description = "$modelType Service";
                break;
        }

        return $description . "\nReference: $reference";
    }

    /**
     * Generate description for the invoice
     */
    protected function generateDescription($payable): string
    {
        $modelType = class_basename($payable);
        $reference = $payable->reference_number ?? $payable->id;

        return "Payment for $modelType service - Reference: $reference";
    }

    /**
     * Get logo path or base64 data
     */
    protected function getLogo(): ?string
    {
        //TODO: add logo
        $logoPath = public_path("images/logo.png");

        if (file_exists($logoPath)) {
            return $logoPath;
        }

        return null; // No logo available
    }

    /**
     * Generate filename for the invoice
     */
    protected function generateFilename(Payment $payment): string
    {
        $payable = $payment->payable;
        $modelType = class_basename($payable);
        $reference = $payable->reference_number ?? $payable->id;

        return "Invoice-$modelType-$reference-$payment->id.pdf";
    }

    /**
     * Stream invoice as PDF
     * @throws Exception
     */
    public function streamInvoice(Payment $payment): Response
    {
        $invoice = $this->generateInvoice($payment);

        return $invoice->stream();
    }

    /**
     * Save invoice to storage and return path
     * @throws Exception
     */
    public function saveInvoice(Payment $payment): string
    {
        $invoice = $this->generateInvoice($payment);
        $filename = $this->generateFilename($payment);
        $path = "invoices/" . $filename;

        // Get PDF output and save to storage
        Storage::disk("public")->put($path, $invoice->getPdfOutput());

        return $path;
    }

    /**
     * Get PDF output as string
     * @throws Exception
     */
    public function getPdfOutput(Payment $payment): string
    {
        $invoice = $this->generateInvoice($payment);

        return $invoice->getPdfOutput();
    }
}
