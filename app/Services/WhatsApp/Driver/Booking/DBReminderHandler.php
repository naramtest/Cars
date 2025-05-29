<?php

namespace App\Services\WhatsApp\Driver\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Services\WhatsApp\Traits\BookingNotificationData;

class DBReminderHandler extends WhatsAppAbstractHandler
{
    use BookingNotificationData;

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
                        "text" => $modelData->reference_number,
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
                            "url" => templateUrlReplaceParameter(
                                route("booking.driver.confirmation", [
                                    "booking" => "PLACEHOLDER_VALUE",
                                ])
                            ),
                            "example" => ["BOK-202504-0001"],
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
}
