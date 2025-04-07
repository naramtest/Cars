<?php

namespace App\Services\WhatsApp\Admin\Vehicle;

use App\Filament\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class VehicleRegistrationExpiryHandler extends WhatsAppAbstractHandler
{
    /** @var Vehicle $modelData */
    public function prepareBodyData($modelData): array
    {
        $daysUntilExpiry = now()->diffInDays(
            $modelData->registration_expiry_date,
            false
        );

        return $this->formatBodyParameters([
            $modelData->name, // 1 - Vehicle name
            $modelData->model, // 2 - Vehicle model
            $modelData->license_plate, // 3 - License plate
            $modelData->registration_expiry_date->format("Y-m-d"), // 4 - Expiry date
            (string) $daysUntilExpiry, // 5 - Days until expiry
            $modelData->driver
                ? $modelData->driver->full_name
                : "No driver assigned", // 6 - Driver name
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
                        "⚠️ *Vehicle Registration Expiry Alert* ⚠️\n\n" .
                        "The following vehicle registration is expiring soon:\n\n" .
                        "🚗 Vehicle: *{{1}} ({{2}})*\n" .
                        "🔢 License Plate: *{{3}}*\n" .
                        "📅 Expiry Date: *{{4}}*\n" .
                        "⏰ Days Remaining: *{{5}}*\n" .
                        "👤 Assigned Driver: {{6}}\n" .
                        "🚫 This is an automated reminder. Please do not reply.",
                    "example" => [
                        "body_text" => [
                            [
                                "Toyota Camry", // {{1}} Vehicle name
                                "2021", // {{2}} Vehicle model
                                "ABC-1234", // {{3}} License plate
                                "2025-05-01", // {{4}} Registration expiry date
                                "7", // {{5}} Days until expiry
                                "John Doe", // {{6}} Driver name
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "View Vehicle Details",
                            "url" => templateUrl(
                                VehicleResource::getUrl() . "/{{1}}"
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

    public function isEnabled(): bool
    {
        return true;
    }
}
