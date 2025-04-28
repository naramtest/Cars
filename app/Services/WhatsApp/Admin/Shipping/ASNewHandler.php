<?php

namespace App\Services\WhatsApp\Admin\Shipping;

use App\Filament\Resources\ShippingResource;
use App\Models\Shipping;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Traits\HasAdminPhoneNumbers;

class ASNewHandler extends WhatsAppAbstractHandler
{
    use HasAdminPhoneNumbers;

    /**
     * Prepares body data for shipping notification
     *
     * @param Shipping $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $driver = $modelData->driver;

        return $this->formatBodyParameters([
            $modelData->reference_number ?: "Undefined", // 1 - Shipping reference
            $modelData->getCustomer()->name, // 2 - Customer name
            $modelData->getCustomer()->phone_number, // 3 - Customer phone
            $driver ? $driver->full_name : "Not assigned", // 4 - Driver name
            $modelData->pickup_address, // 5 - Pickup address
            $modelData->delivery_address, // 6 - Delivery address
            $modelData->notes ?: "No notes provided", // 7 - Notes
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
                        "📦 New Shipping Created 📦\n\n" .
                        "Reference: {{1}}\n" .
                        "Client: {{2}} ({{3}})\n\n" .
                        "Driver: {{4}}\n" .
                        "Locations:\n" .
                        "- Pickup: {{5}}\n" .
                        "- Delivery: {{6}}\n\n" .
                        "Notes: {{7}}\n" .
                        "🚫 This is an automated message. Please do not reply.",
                    "example" => [
                        "body_text" => [
                            [
                                "SHP-202504-0001", // {{1}} Reference
                                "John Smith", // {{2}} Client name
                                "+971550000000", // {{3}} Client phone
                                "Ahmed Hassan", // {{4}} Driver name
                                "Dubai Marina", // {{7}} Pickup address
                                "Dubai Airport Terminal 3", // {{8}} Delivery address
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
}
