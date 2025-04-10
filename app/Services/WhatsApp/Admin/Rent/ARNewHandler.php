<?php

namespace App\Services\WhatsApp\Admin\Rent;

use App\Filament\Resources\RentResource;
use App\Models\Rent;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class ARNewHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for rent notification
     *
     * @param Rent $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $vehicle = $modelData->vehicle;

        return $this->formatBodyParameters([
            $modelData->reference_number ?: "Undefined", // 1 - Rent reference
            $modelData->client_name, // 2 - Customer name
            $modelData->client_phone, // 3 - Customer phone
            $vehicle->name, // 4 - Vehicle name
            $vehicle->model, // 5 - Vehicle model
            $vehicle->license_plate, // 6 - Vehicle plate
            $modelData->rental_start_date->format("Y-m-d"), // 7 - Start date
            $modelData->rental_start_date->format("H:i"), // 8 - Start time
            $modelData->rental_end_date->format("Y-m-d"), // 9 - End date
            $modelData->rental_end_date->format("H:i"), // 10 - End time
            $modelData->pickup_address, // 11 - Pickup address
            $modelData->drop_off_address, // 12 - Drop off address
            $modelData->description ?: "No specific details", // 13 - Description/notes
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
                        "🚗 New Rental Created 🚗\n\n" .
                        "Reference: {{1}}\n" .
                        "Client: {{2}} ({{3}})\n\n" .
                        "Vehicle Information:\n" .
                        "- Name: {{4}}\n" .
                        "- Model: {{5}}\n" .
                        "- License Plate: {{6}}\n\n" .
                        "Rental Period:\n" .
                        "- From: {{7}} at {{8}}\n" .
                        "- To: {{9}} at {{10}}\n" .
                        "Address:\n" .
                        "- Pickup: {{11}}\n" .
                        "- Drop-off: {{12}}\n\n" .
                        "Additional Info: {{13}}\n\n" .
                        "This is an automated notification. Please do not reply.",
                    "example" => [
                        "body_text" => [
                            [
                                "RNT-202504-0001", // {{1}} Reference
                                "John Smith", // {{2}} Client name
                                "+971550000000", // {{3}} Client phone
                                "Mercedes-Benz", // {{4}} Vehicle name
                                "S-Class", // {{5}} Vehicle model
                                "ABC123", // {{6}} License plate
                                "2025-05-10", // {{7}} Start date
                                "14:00", // {{8}} Start time
                                "2025-05-15", // {{9}} End date
                                "12:00", // {{10}} End time
                                "Dubai Marina", // {{11}} Pickup address
                                "Dubai Airport Terminal 3", // {{12}} Drop-off address
                                "Customer requires child seat", // {{13}} Description
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
                                RentResource::getUrl() . "/{{1}}"
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
