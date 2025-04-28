<?php

namespace App\Services\WhatsApp\Customer\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CBNewHandler extends WhatsAppAbstractHandler
{
    /** @var Booking $modelData */
    public function prepareBodyData($modelData): array
    {
        $driver = $modelData->driver;
        $vehicle = $modelData->vehicle;

        return $this->formatBodyParameters([
            $modelData->getCustomer()->name, // 1 - Client Name
            $modelData->reference_number ?: "Undefined", // 2 - Booking reference
            $vehicle->model, // 3 - Vehicle model
            $vehicle->license_plate, // 4 - Vehicle plate
            $driver->full_name ?? " ", // 5 - Driver name
            $modelData->start_datetime->format("Y-m-d"), // 6 - Pickup date
            $modelData->start_datetime->format("H:i"), // 7 - Pickup time
            $modelData->pickup_address, // 8 - Pickup location
            $modelData->formatted_total_price, // 9 - Price
        ]);
    }

    public function prepareButtonData($modelData): array
    {
        $driver = $modelData->driver;

        return [
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 1,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $driver->phone_number,
                    ],
                ],
            ],
        ];
    }

    public function getGroup(): string
    {
        return "customer";
    }

    /** @var Booking $data */

    public function phoneNumbers($data)
    {
        return $data->getCustomer()->phone_number;
    }

    public function facebookTemplateData(): array
    {
        return [
            "name" => $this->getTemplateName(), // e.g., client_booking_confirmation
            "language" => "en_US",
            "category" => "UTILITY",
            "components" => [
                [
                    "type" => "BODY",
                    "text" =>
                        "Hi {{1}}, your booking has been confirmed! 🎉\n\n" .
                        "🧾 Booking Reference: {{2}}\n" .
                        "🚗 Vehicle: {{3}} ({{4}})\n" .
                        "👨‍✈️ Driver: {{5}}\n" .
                        "📅 Pickup Time: {{6}} at {{7}}\n" .
                        "📍 Pickup Location: {{8}}\n" .
                        "💵 Total Price: {{9}}\n\n" .
                        "If you have any questions, feel free to contact us.\n\n" .
                        "🚫 Note: This is an automated message. Please do not reply directly.",
                    "example" => [
                        "body_text" => [
                            [
                                "Alice Doe", // {{1}} Client Name (you can pass this manually or via $modelData)
                                "REF12345", // {{2}} Booking reference
                                "Toyota Camry", // {{3}} Vehicle model
                                "XYZ-9876", // {{4}} Vehicle plate
                                "John", // {{5}} Driver name
                                "2025-04-05", // {{6}} Pickup date
                                "10:00 AM", // {{7}} Pickup time
                                "Downtown Station", // {{8}} Pickup address
                                "$75.00", // {{9}} Price
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "Contact Driver",

                            "url" => templateUrlReplaceParameter(
                                route("driver.contact", [
                                    "driver" => "PLACEHOLDER_VALUE",
                                ])
                            ),
                            "example" => ["1"],
                        ],
                        [
                            "type" => "URL",
                            "text" => "Contact Support",
                            "url" => templateUrl(route("whatsapp.contact")),
                        ],
                    ],
                ],
            ],
        ];
    }
}
