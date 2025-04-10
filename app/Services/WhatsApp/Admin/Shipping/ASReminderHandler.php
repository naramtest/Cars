<?php

namespace App\Services\WhatsApp\Admin\Shipping;

use App\Filament\Resources\ShippingResource;
use App\Models\Shipping;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class ASReminderHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for shipping reminder notification
     *
     * @param Shipping $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $driver = $modelData->driver;

        // Calculate hours remaining until delivery/pickup
        $hoursRemaining = now()->diffInHours($modelData->pick_up_at);
        $timeRemaining =
            $hoursRemaining > 1 ? "$hoursRemaining hours" : "1 hour";

        return $this->formatBodyParameters([
            $modelData->reference_number ?: "Undefined", // 1 - Shipping reference
            $modelData->client_name, // 2 - Customer name
            $modelData->client_phone, // 3 - Customer phone
            $driver ? $driver->full_name : "Not assigned", // 4 - Driver name
            $modelData->pickup_address, // 5 - Pickup address
            $modelData->delivery_address, // 6 - Delivery address
            $modelData->pick_up_at
                ? $modelData->pick_up_at->format("Y-m-d H:i")
                : "Not scheduled", // 7 - Scheduled time
            $timeRemaining, // 8 - Time remaining
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
                        "⏰ REMINDER: Upcoming Shipping ⏰\n\n" .
                        "Reference: {{1}}\n" .
                        "Client: {{2}} ({{3}})\n\n" .
                        "Driver: {{4}}\n" .
                        "Locations:\n" .
                        "- Pickup: {{5}}\n" .
                        "- Delivery: {{6}}\n\n" .
                        "Scheduled Time: {{7}} (in approximately {{8}})\n" .
                        "🚫 This is an automated reminder. Please do not reply.",
                    "example" => [
                        "body_text" => [
                            [
                                "SHP-202504-0001", // {{1}} Reference
                                "John Smith", // {{2}} Client name
                                "+971550000000", // {{3}} Client phone
                                "Ahmed Hassan", // {{4}} Driver name

                                "Dubai Marina", // {{5}} Pickup address
                                "Dubai Airport Terminal 3", // {{6}} Delivery address
                                "2025-04-15 14:00", // {{7}} Scheduled time
                                "2 hours", // {{8}} Time remaining
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
