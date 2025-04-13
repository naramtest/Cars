<?php

namespace App\Services\WhatsApp\Driver\Shipping;

use App\Filament\Resources\ShippingResource;
use App\Models\Shipping;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class DSNewHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for driver shipping notification
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
            $modelData->pick_up_at
                ? $modelData->pick_up_at->format("Y-m-d H:i")
                : "Not scheduled", // 7 - Pickup date/time
            $modelData->notes ?: "No specific details", // 8 - Notes
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
                        "text" => $modelData->id,
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
                        "Hi {{1}}, you have been assigned to a shipping task.\n\n" .
                        "🧾 Shipping Reference: {{2}}\n" .
                        "👤 Client: {{3}} ({{4}})\n\n" .
                        "📍 Pickup Location: {{5}}\n" .
                        "🏁 Delivery Location: {{6}}\n" .
                        "⏰ Pickup Time: {{7}}\n\n" .
                        "Notes: {{8}}\n\n" .
                        "Please confirm receipt of this message and prepare for pickup.",
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
                                "Fragile items, handle with care", // {{8}} Notes
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "View  Details",
                            "url" => templateUrl(
                                ShippingResource::getUrl() . "/{{1}}"
                            ),
                            "example" => ["1"],
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
