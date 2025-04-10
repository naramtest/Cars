<?php

namespace App\Services\WhatsApp\Admin\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Services\WhatsApp\Traits\BookingNotificationData;

class ABReminderHandler extends WhatsAppAbstractHandler
{
    use BookingNotificationData;

    public function prepareButtonData($modelData): array
    {
        return [];
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
                        "Hi , this is a reminder for your upcoming booking.\n\n Driver : {{1}} \n\n🧾 Booking Reference: {{2}}\n👤 Customer: {{3}} ({{4}})\n🚗 Vehicle: {{5}} ({{6}})\n📅 Pickup Time: {{7}} at {{8}}\n📍 Pickup Location: {{9}}\n🏁 Drop-off Location: {{10}}\n\n{{11}}\n\n🚫 Note: This is an automated reminder. Do not reply to this message.",
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
}
