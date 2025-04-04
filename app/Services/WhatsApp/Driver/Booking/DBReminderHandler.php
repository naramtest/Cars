<?php

namespace App\Services\WhatsApp\Driver\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppTemplate;

class DBReminderHandler extends WhatsAppTemplate
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

    /** @var Booking $modelData */

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
                        "text" => route("booking.driver.confirmation", [
                            "booking" => Booking::first(),
                        ]),
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
                        "Hi {{1}}, this is a reminder for your upcoming booking.\n\n🧾 Booking Reference: {{2}}\n👤 Customer: {{3}} ({{4}})\n🚗 Vehicle: {{5}} ({{6}})\n📅 Pickup Time: {{7}} at {{8}}\n📍 Pickup Location: {{9}}\n🏁 Drop-off Location: {{10}}\n\n{{11}}\n\n🚫 Note: This is an automated reminder. Do not reply to this message.\n\n✅ When the booking is complete, please press the button below.",
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
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "Mark as Completed",
                            "url" => route("booking.driver.confirmation", [
                                "booking" => Booking::first(),
                            ]),
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
        /** @var  Booking $data */
        return $data->driver->phone_number;
    }

    public function isEnabled(): bool
    {
        // TODO: Implement isEnabled() method.
        return true;
    }
}
