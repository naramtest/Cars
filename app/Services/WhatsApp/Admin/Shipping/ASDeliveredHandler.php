<?php

namespace App\Services\WhatsApp\Admin\Shipping;

use App\Filament\Resources\ShippingResource;
use App\Models\Shipping;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class ASDeliveredHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for shipping delivered notification
     *
     * @param Shipping $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $driver = $modelData->driver;

        return $this->formatBodyParameters([
            $modelData->reference_number ?: "Undefined", // 1 - Shipping reference
            $modelData->client_name, // 2 - Customer name
            $modelData->client_phone, // 3 - Customer phone
            $driver ? $driver->full_name : "Not assigned", // 4 - Driver name
            $modelData->pickup_address, // 5 - Pickup address
            $modelData->delivery_address, // 6 - Delivery address
            $modelData->delivered_at
                ? $modelData->delivered_at->format("Y-m-d H:i")
                : now()->format("Y-m-d H:i"), // 7 - Delivery time
            $modelData->delivery_notes ?: "No delivery notes provided", // 8 - Delivery notes
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
                        "text" => $modelData->id . "/edit",
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
                        "🚚 Shipping Delivered Successfully 🚚\n\n" .
                        "Reference: {{1}}\n" .
                        "Client: {{2}} ({{3}})\n" .
                        "Driver: {{4}}\n\n" .
                        "Shipping Route:\n" .
                        "- From: {{5}}\n" .
                        "- To: {{6}}\n\n" .
                        "Delivered At: {{7}}\n\n" .
                        "Delivery Notes: {{8}}\n\n" .
                        "🚫 This is an automated message. Please do not reply.",
                    "example" => [
                        "body_text" => [
                            [
                                "SHP-202504-0001", // {{1}} Reference
                                "John Smith", // {{2}} Client name
                                "+971550000000", // {{3}} Client phone
                                "Ahmed Hassan", // {{4}} Driver name
                                "Dubai Marina", // {{5}} Pickup address
                                "Dubai Airport Terminal 3", // {{6}} Delivery address
                                "2025-04-15 16:30", // {{7}} Delivery time
                                "Delivered to reception desk, signed by Sarah.", // {{8}} Delivery notes
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
                            "url" => templateUrl(
                                ShippingResource::getUrl() . "/{{1}}"
                            ),
                            "example" => ["1/edit"],
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

    public function phoneNumbers($data)
    {
        // TODO: Get admin numbers from settings or database
        return ["+971562065970"]; // Replace with appropriate admin phone numbers
    }
}
