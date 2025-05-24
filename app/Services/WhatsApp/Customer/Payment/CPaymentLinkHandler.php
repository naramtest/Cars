<?php

namespace App\Services\WhatsApp\Customer\Payment;

use App\Models\Abstract\Payable;
use App\Models\Payment;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CPaymentLinkHandler extends WhatsAppAbstractHandler
{
    public function prepareBodyData($modelData): array
    {
        /** @var Payment $payment */
        $payment = $modelData->payment;
        $modelType = class_basename($modelData);

        return $this->formatBodyParameters([
            $modelData->getCustomer()->name, // 1 - Customer name
            $modelType, // 2 - Model type (Booking, Rent, Shipping)
            $modelData->reference_number, // 3 - Reference number
            $payment->formatted_amount, // 4 - Formatted price
            $payment->note ?: "No additional notes", // 5 - Payment note
        ]);
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
                        "Hello {{1}},\n\n" .
                        "Your payment for {{2}} with reference number {{3}} is pending.\n\n" .
                        "💰 Amount Due: {{4}}\n\n" .
                        "📝 Note: {{5}}\n\n" .
                        "Please use the link below to complete your payment.\n\n" .
                        "If you have any questions or need assistance, please contact our support team.",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "Booking", // {{2}} Model type
                                "BOK-202504-0001", // {{3}} Reference number
                                "AED 2,500.00", // {{4}} Amount
                                "Payment for booking services", // {{5}} Payment note
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
                            "url" => templateUrlReplaceParameter(
                                route("payment.pay.show", [
                                    "payment" => "PLACEHOLDER_VALUE",
                                ])
                            ),
                            "example" => ["1"],
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
