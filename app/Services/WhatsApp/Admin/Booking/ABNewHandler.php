<?php

namespace App\Services\WhatsApp\Admin\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class ABNewHandler extends WhatsAppAbstractHandler
{
    /** @var Booking $modelData */
    public function prepareBodyData($modelData): array
    {
        $driver = $modelData->driver;
        $vehicle = $modelData->vehicle;

        return $this->formatBodyParameters([
            $driver->full_name ?? " ", // 1 - Driver's name
            $modelData->reference_number ?: "Undefined", // 2 - Booking ref
            $modelData->client_name, // 3 - Customer name
            $modelData->client_phone, // 4 - Customer phone
            $vehicle->model, // 5 - Vehicle model
            $vehicle->license_plate, // 6 - Vehicle plate
            $modelData->start_datetime->format("Y-m-d"),
            // 7 - Pickup date
            $modelData->start_datetime->format("H:i"), // 8 - Pickup time
            $modelData->pickup_address, // 9 - Pickup location
            $modelData->destination_address ?: "Undefined", // 10 - Dropoff location
            $modelData->notes ?? "nothing", // 11 - Optional instructions
        ]);
    }

    public function prepareButtonData($modelData): array
    {
        return [];
    }

    public function getGroup(): string
    {
        return "admin";
    }

    public function phoneNumbers($data)
    {
        //TODO: get it from database
        /** @var  Booking $data */
        return ["+971562065970"];
    }

    public function isEnabled(): bool
    {
        // TODO: Implement isEnabled() method.
        return true;
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
                        "Hi ,a new booking has been Added.\n\n Driver: {{1}} \n\n🧾 Booking Reference: {{2}}\n👤 Customer: {{3}} ({{4}})\n🚗 Vehicle: {{5}} ({{6}})\n📅 Pickup Time: {{7}} at {{8}}\n📍 Pickup Location: {{9}}\n🏁 Drop-off Location: {{10}}\n\n{{11}}\n\n🚫 Note: This is an automated reminder. Do not reply to this message.",
                    "example" => [
                        "body_text" => [
                            [
                                "John", // {{1}} Driver name
                                "REF12345", // {{2}} Booking reference
                                "Alice Doe", // {{3}} Customer name
                                "+123456789", // {{4}} Customer phone
                                "Toyota Camry", // {{5}} Vehicle name
                                "XYZ-9876", // {{6}} Vehicle plate
                                "2025-04-05", // {{7}} Pickup date
                                "10:00 AM", // {{8}} Pickup time
                                "Downtown Station", // {{9}} From
                                "Airport Terminal 3", // {{10}} To
                                "Special instructions", // {{11}} Optional notes
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
