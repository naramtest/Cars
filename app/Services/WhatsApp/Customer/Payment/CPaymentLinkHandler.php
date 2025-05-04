<?php

namespace App\Services\WhatsApp\Customer\Payment;

use App\Models\Abstract\Payable;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use Str;

class CPaymentLinkHandler extends WhatsAppAbstractHandler
{
    public function prepareBodyData($modelData): array
    {
        $payment = $modelData->payment;
        $modelType = class_basename($modelData);

        return $this->formatBodyParameters([
            $modelData->getCustomer()->name, // 1 - Customer name
            $modelType, // 2 - Model type (Booking, Rent, Shipping)
            $modelData->reference_number, // 3 - Reference number
            $payment->formatted_amount, // 4 - Formatted price
            $payment->payment_link, // 5 - Payment link
            $payment->payment_link_expires_at
                ? $payment->payment_link_expires_at->format("Y-m-d")
                : "Not specified", // 6 - Expiry date
        ]);
    }

    public function prepareButtonData($modelData): array
    {
        $paymentLink = Str::after($modelData->payment->payment_link, ".com/");
        return [
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 0,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $paymentLink,
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
                        "Hello {{1}},\n\n" .
                        "Your payment for {{2}} with reference number {{3}} is pending.\n\n" .
                        "💰 Amount Due: {{4}}\n\n" .
                        "Please use the link below to complete your payment:\n{{5}}\n\n" .
                        "⏰ This payment link will expire on: {{6}}\n\n" .
                        "If you have any questions or need assistance, please contact our support team.",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "Booking", // {{2}} Model type
                                "BOK-202504-0001", // {{3}} Reference number
                                "AED 2,500.00", // {{4}} Amount
                                "https://buy.stripe.com/test_dR615hgyKeAg0wg8wG", // {{5}} Payment link
                                "2025-05-15", // {{6}} Expiry date
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "Pay Now",
                            "url" => "https://buy.stripe.com/{{1}}",
                            "example" => ["test_dR615hgyKeAg0wg8wG"],
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

    public function getGroup(): string
    {
        return "customer";
    }

    public function phoneNumbers($data)
    {
        /** @var Payable $data */
        return $data->getCustomer()->phone_number;
    }
}
