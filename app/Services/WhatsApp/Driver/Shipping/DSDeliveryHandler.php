<?php

namespace App\Services\WhatsApp\Driver\Shipping;

use App\Models\Shipping;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class DSDeliveryHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for driver shipping delivery notification
     *
     * @param Shipping $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        return $this->formatBodyParameters([
            $modelData->driver->full_name ?? "Driver", // 1 - Driver name
            $modelData->reference_number ?: "Undefined", // 2 - Shipping reference
            $modelData->client_name, // 3 - Customer name
            $modelData->client_phone, // 4 - Customer phone
            $modelData->pickup_address, // 5 - Pickup address
            $modelData->delivery_address, // 6 - Delivery address
            $modelData->received_at
                ? $modelData->received_at->format("Y-m-d H:i")
                : "Not recorded", // 7 - Pickup time
        ]);
    }

    /** @var Shipping $modelData */
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
                        "text" => $modelData->reference_number ?? "N/A",
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
                        "Hi {{1}}, you have picked up the package with reference: {{2}}.\n\n" .
                        "👤 Client: {{3}} ({{4}})\n\n" .
                        "📍 Pickup: {{5}}\n" .
                        "🏁 Delivery: {{6}}\n" .
                        "⏰ Picked up at: {{7}}\n\n" .
                        "When you deliver the package, please click the button below to confirm delivery and add delivery notes if needed.",
                    "example" => [
                        "body_text" => [
                            [
                                "Ahmed Hassan", // {{1}} Driver name
                                "SHP-202504-0001", // {{2}} Reference
                                "John Smith", // {{3}} Client name
                                "+971550000000", // {{4}} Client phone
                                "Dubai Marina", // {{5}} Pickup address
                                "Dubai Airport Terminal 3", // {{6}} Delivery address
                                "2025-04-15 14:00", // {{7}} Pickup time
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "Confirm Delivery",
                            "url" => templateUrlReplaceParameter(
                                route("shipping.driver.delivery", [
                                    "shipping" => "PLACEHOLDER_VALUE",
                                ])
                            ),
                            "example" => ["SHP-202503-0001"],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getGroup(): string
    {
        return "driver";
    }

    public function phoneNumbers($data)
    {
        /** @var Shipping $data */
        return $data->driver ? $data->driver->phone_number : null;
    }
}
