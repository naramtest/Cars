<?php

namespace App\Services\WhatsApp\Admin\Payment;

use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Traits\HasAdminPhoneNumbers;

class APaymentSuccessHandler extends WhatsAppAbstractHandler
{
    use HasAdminPhoneNumbers;

    public function prepareBodyData($modelData): array
    {
        $customer = $modelData->getCustomer();
        $modelType = class_basename($modelData);

        // Get additional context based on the payable type
        $payment = $modelData->payment;
        $contextInfo = $this->getContextInfo($modelData, $modelType);

        return $this->formatBodyParameters([
            $customer->name, // 1 - Customer name
            $customer->phone_number, // 2 - Customer phone
            $modelType, // 3 - Service type (Booking, Rent, Shipping)
            $modelData->reference_number ?? $modelData->id, // 4 - Reference number
            $payment->formatted_amount, // 5 - Payment amount
            $payment->paid_at
                ? $payment->paid_at->format("Y-m-d H:i")
                : now()->format("Y-m-d H:i"), // 6 - Payment date
            $payment->payment_method->getLabel(), // 7 - Payment method
            $contextInfo, // 8 - Additional context info
        ]);
    }

    /**
     * Get context information based on payable type
     */
    private function getContextInfo($payable, string $modelType): string
    {
        switch ($modelType) {
            case "Booking":
                $vehicle = $payable->vehicle;
                return "Vehicle: {$vehicle->name} ({$vehicle->license_plate}) - From: {$payable->start_datetime->format(
                    "M j, H:i"
                )} to {$payable->end_datetime->format("M j, H:i")}";

            case "Rent":
                $vehicle = $payable->vehicle;
                return "Vehicle: {$vehicle->name} ({$vehicle->license_plate}) - Rental: {$payable->rental_start_date->format(
                    "M j"
                )} to {$payable->rental_end_date->format("M j")}";

            case "Shipping":
                $itemsCount = $payable->items()->count();
                return "Shipping: {$itemsCount} items, {$payable->total_weight}kg - From: {$payable->pickup_address} to: {$payable->delivery_address}";

            default:
                return "Service completed successfully";
        }
    }

    public function prepareButtonData($modelData): array
    {
        return [
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 0,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $modelData->payment->id,
                    ],
                ],
            ],
        ];
    }

    public function facebookTemplateData(): array
    {
        return [
            "name" => $this->getTemplateName(),
            "language" => "en_US",
            "category" => "UTILITY",
            "components" => [
                [
                    "type" => "BODY",
                    "text" =>
                        "💰 *Payment Received Successfully* 💰\n\n" .
                        "Customer: {{1}} ({{2}})\n\n" .
                        "Service: {{3}}\n" .
                        "Reference: {{4}}\n" .
                        "Amount: *{{5}}*\n" .
                        "Paid At: {{6}}\n" .
                        "Method: {{7}}\n\n" .
                        "Details: {{8}}\n\n" .
                        "🚫 This is an automated notification. Please do not reply.",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "+971550000000", // {{2}} Customer phone
                                "Booking", // {{3}} Service type
                                "BOK-202504-0001", // {{4}} Reference number
                                "AED 2,500.00", // {{5}} Payment amount
                                "2025-05-24 14:30", // {{6}} Payment date
                                "Stripe", // {{7}} Payment method
                                "Vehicle: Mercedes S-Class (ABC-1234) - From: May 24, 14:00 to May 25, 12:00", // {{8}} Context info
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "View Details",
                            "url" => templateUrlReplaceParameter(
                                route("payable.redirect", [
                                    "payment" => "PLACEHOLDER_VALUE",
                                ])
                            ), // This will be dynamically set
                            "example" => ["1"],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getGroup(): string
    {
        return "admin";
    }
}
