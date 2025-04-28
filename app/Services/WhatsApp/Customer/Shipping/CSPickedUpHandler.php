<?php

namespace App\Services\WhatsApp\Customer\Shipping;

use App\Models\Shipping;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CSPickedUpHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for customer shipping pickup notification
     *
     * @param Shipping $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        return $this->formatBodyParameters([
            $modelData->customer->name, // 1 - Customer name
            $modelData->reference_number ?: "Undefined", // 2 - Shipping reference
            $modelData->pickup_address, // 3 - Pickup address
            $modelData->delivery_address, // 4 - Delivery address
            $modelData->driver ? $modelData->driver->full_name : "Our Driver", // 5 - Driver name
            $modelData->received_at
                ? $modelData->received_at->format("Y-m-d H:i")
                : now()->format("Y-m-d H:i"), // 6 - Pickup time
        ]);
    }

    public function prepareButtonData($modelData): array
    {
        $driver = $modelData->driver;

        return [
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 1,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $driver->phone_number,
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
                        "Good news! Your package has been picked up. 📦\n\n" .
                        "🧾 Reference Number: {{2}}\n\n" .
                        "📍 Picked up from: {{3}}\n\n" .
                        "🏁 Will be delivered to: {{4}}\n\n" .
                        "👨‍✈️ Driver: {{5}}\n\n" .
                        "⏰ Picked up at: {{6}}\n\n" .
                        "Your package is now on its way to the delivery address. We'll notify you once it has been delivered. If you have any questions, please contact our support team.",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "SHP-202504-0001", // {{2}} Reference number
                                "Dubai Marina, Building 7", // {{3}} Pickup address
                                "Dubai Airport Terminal 3", // {{4}} Delivery address
                                "Ahmed Hassan", // {{5}} Driver name
                                "2025-04-15 14:00", // {{6}} Pickup time
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "Contact Driver",

                            "url" => templateUrlReplaceParameter(
                                route("driver.contact", [
                                    "driver" => "PLACEHOLDER_VALUE",
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
        /** @var Shipping $data */
        return $data->customer->phone_number;
    }
}
