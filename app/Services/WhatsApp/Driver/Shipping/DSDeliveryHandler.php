<?php

namespace App\Services\WhatsApp\Driver\Shipping;

use App\Helpers\TokenHelper;
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
        // Generate a token containing shipping and driver info
        $token = TokenHelper::generatePickupToken(
            $modelData->id,
            $modelData->driver_id,
            $modelData->pick_up_at
        );

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
                            "url" => route("shipping.driver.delivery", [
                                "token" => "PLACEHOLDER_VALUE",
                            ]),
                            "example" => [
                                "eyJpdiI6IktJWXJic0I4cTZZK05md1dRUGVPc3c9PSIsInZhbHVlIjoic1pwcFZaUzg5L0U1Q1cyOCtrZVY5OFFJUm0rMXZSaTlYblpiTTZSM0Nkd01aS3FPc2lXTm93Q0ZIaVJDMzlnVG4wM3pUMG8wQmMwMDhVaDhVcU51VWc9PSIsIm1hYyI6ImMxYTUyYzUzNzFmN2IzYThkNzAyNDhiNjljMDAzODExYjBhNDA5MjUyZjUxYThlYTdjMjhlYTQ2ZjE5OGQ2ZGUiLCJ0YWciOiIifQ==",
                            ],
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
