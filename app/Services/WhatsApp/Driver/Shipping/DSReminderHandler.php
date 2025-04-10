<?php

namespace App\Services\WhatsApp\Driver\Shipping;

use App\Helpers\TokenHelper;
use App\Models\Shipping;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class DSReminderHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for driver shipping reminder notification
     *
     * @param Shipping $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $pickupTime = $modelData->pick_up_at
            ? $modelData->pick_up_at->format("Y-m-d H:i")
            : "Not scheduled";
        $timeRemaining = $modelData->pick_up_at
            ? now()->diffForHumans($modelData->pick_up_at)
            : "30 minutes";

        return $this->formatBodyParameters([
            $modelData->driver->full_name ?? "Driver", // 1 - Driver name
            $modelData->reference_number ?: "Undefined", // 2 - Shipping reference
            $modelData->client_name, // 3 - Customer name
            $modelData->client_phone, // 4 - Customer phone
            $modelData->pickup_address, // 5 - Pickup address
            $modelData->delivery_address, // 6 - Delivery address
            $pickupTime, // 7 - Pickup time
            $timeRemaining, // 8 - Time remaining
            $modelData->notes ?: "No specific details", // 9 - Notes
        ]);
    }

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
                        "⏰ REMINDER: Hi {{1}}, you have a scheduled pickup in about 30 minutes.\n\n" .
                        "🧾 Shipping Reference: {{2}}\n" .
                        "👤 Client: {{3}} ({{4}})\n\n" .
                        "📍 Pickup Location: {{5}}\n" .
                        "🏁 Delivery Location: {{6}}\n" .
                        "⏰ Pickup Time: {{7}} (in approximately {{8}})\n\n" .
                        "Notes: {{9}}\n\n" .
                        "Please ensure you are on track to arrive at the pickup location on time.",
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
                                "30 minutes", // {{8}} Time remaining
                                "Fragile items, handle with care", // {{9}} Notes
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "Confirm Pickup",
                            "url" => route("shipping.driver.pickup", [
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

    public function isEnabled(): bool
    {
        return true;
    }
}
