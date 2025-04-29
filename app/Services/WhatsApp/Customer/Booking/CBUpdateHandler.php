<?php

namespace App\Services\WhatsApp\Customer\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CBUpdateHandler extends WhatsAppAbstractHandler
{
    /** @var Booking $modelData */
    public function prepareBodyData($modelData): array
    {
        $vehicle = $modelData->vehicle;
        $driver = $modelData->driver;

        return $this->formatBodyParameters([
            $modelData->getCustomer()->name, // 1 - Customer name
            $modelData->reference_number, // 2 - Booking reference
            $vehicle->name, // 3 - Vehicle name
            $vehicle->model, // 4 - Vehicle model
            $vehicle->license_plate, // 5 - Vehicle license plate
            $driver ? $driver->full_name : "Not assigned", // 6 - Driver name
            $modelData->start_datetime->format("Y-m-d"), // 7 - Pickup date
            $modelData->start_datetime->format("H:i"), // 8 - Pickup time
            $modelData->end_datetime->format("Y-m-d"), // 9 - End date
            $modelData->end_datetime->format("H:i"), // 10 - End time
            $modelData->pickup_address, // 11 - Pickup address
            $modelData->destination_address ?: "Not specified", // 12 - Destination address
            $modelData->formatted_total_price, // 13 - Price
            $modelData->notes ?: "No additional notes", // 14 - Notes
        ]);
    }

    public function prepareButtonData($modelData): array
    {
        $driver = $modelData->driver;

        return [
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 0,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $driver->id,
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
            "name" => $this->getTemplateName(),
            "language" => "en_US",
            "category" => "UTILITY",
            "components" => [
                [
                    "type" => "BODY",
                    "text" =>
                        "Hi {{1}},\n\n" .
                        "Your booking with reference number {{2}} has been updated.\n\n" .
                        "🚗 Vehicle Details:\n" .
                        "- Make: {{3}}\n" .
                        "- Model: {{4}}\n" .
                        "- License Plate: {{5}}\n\n" .
                        "👨‍✈️ Driver: {{6}}\n\n" .
                        "📅 Booking Period:\n" .
                        "- Start: {{7}} at {{8}}\n" .
                        "- End: {{9}} at {{10}}\n\n" .
                        "📍 Locations:\n" .
                        "- Pickup: {{11}}\n" .
                        "- Destination: {{12}}\n\n" .
                        "💰 Total Price: {{13}}\n\n" .
                        "📝 Notes: {{14}}\n\n" .
                        "If you have any questions about these changes, please contact our support team.",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "BOK-202504-0001", // {{2}} Booking reference
                                "Mercedes", // {{3}} Vehicle name
                                "S-Class", // {{4}} Vehicle model
                                "ABC-1234", // {{5}} License plate
                                "Ahmed Hassan", // {{6}} Driver name
                                "2025-04-15", // {{7}} Pickup date
                                "14:00", // {{8}} Pickup time
                                "2025-04-20", // {{9}} End date
                                "12:00", // {{10}} End time
                                "Dubai Downtown, Burj Khalifa", // {{11}} Pickup location
                                "Dubai Airport Terminal 3", // {{12}} Destination
                                "AED 2,500.00", // {{13}} Price
                                "Customer requested child seat", // {{14}} Notes
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
