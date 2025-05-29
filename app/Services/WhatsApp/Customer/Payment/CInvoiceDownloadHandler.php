<?php

namespace App\Services\WhatsApp\Customer\Payment;

use App\Services\Invoice\InvoiceService;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CInvoiceDownloadHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepare body data for invoice download notification
     */
    public function prepareBodyData($modelData): array
    {
        $payment = $modelData->payment;
        $modelType = class_basename($modelData);

        return $this->formatBodyParameters([
            $modelData->getCustomer()->name, // 1 - Customer name
            $modelType, // 2 - Service type (Booking, Rent, Shipping)
            $payable->reference_number ?? $modelData->id, // 3 - Reference number
            $payment->formatted_amount, // 4 - Payment amount
            $payment->paid_at
                ? $payment->paid_at->format("Y-m-d H:i")
                : "Recently", // 5 - Payment date
        ]);
    }

    /**
     * Prepare button data with invoice download link
     */
    public function prepareButtonData($modelData): array
    {
        $payment = $modelData->payment;

        $token = app(InvoiceService::class)->generateInvoiceToken($payment);

        return [
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 0,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $token,
                    ],
                ],
            ],
        ];
    }

    /**
     * Facebook template data for WhatsApp Business API
     */
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
                        "Hello {{1}},\n\n" .
                        "Great news! Your payment has been successfully processed. 🎉\n\n" .
                        "📋 Service: {{2}}\n" .
                        "🧾 Reference: {{3}}\n" .
                        "💰 Amount: {{4}}\n" .
                        "📅 Payment Date: {{5}}\n\n" .
                        "Your invoice is now ready for download. Click the button below to access your invoice.\n\n" .
                        "Thank you for choosing our services!",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "Booking", // {{2}} Service type
                                "BOK-202504-0001", // {{3}} Reference number
                                "AED 2,500.00", // {{4}} Payment amount
                                "2025-05-24 14:30", // {{5}} Payment date
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "Download Invoice",
                            "url" => route("payment.invoice") . "?{{1}}", // The secure URL will be inserted here
                            "example" => [
                                "expires=1748415640&payment=5&signature=4d311de193e818b5b51ca7fe1e76dc16ec188481c1753c7ff887e890a4181390",
                            ],
                        ],
                        [
                            "type" => "URL",
                            "text" => "Contact Support",
                            "url" => templateUrl(route("whatsapp.contact")),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the group this handler belongs to
     */
    public function getGroup(): string
    {
        return "customer";
    }

    /**
     * Get phone numbers to send the notification to
     */
    public function phoneNumbers($data)
    {
        return $data->getCustomer()->phone_number;
    }
}
