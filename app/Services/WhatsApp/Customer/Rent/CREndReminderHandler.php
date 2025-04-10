<?php

namespace App\Services\WhatsApp\Customer\Rent;

use App\Models\Rent;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CREndReminderHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for customer rent end reminder notification
     *
     * @param Rent $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $vehicle = $modelData->vehicle;

        // Calculate remaining time until end
        $hoursRemaining = now()->diffInHours($modelData->rental_end_date);
        $timeRemaining =
            $hoursRemaining > 1 ? "$hoursRemaining hours" : "1 hour";

        return $this->formatBodyParameters([
            $modelData->client_name, // 1 - Customer name
            $modelData->reference_number, // 2 - Rent reference number
            $vehicle->name, // 3 - Vehicle name
            $vehicle->model, // 4 - Vehicle model
            $vehicle->license_plate, // 5 - Vehicle license plate
            $modelData->rental_end_date->format("Y-m-d"), // 6 - End date
            $modelData->rental_end_date->format("H:i"), // 7 - End time
            $timeRemaining, // 8 - Time remaining
            $modelData->drop_off_address, // 9 - Drop off address
        ]);
    }

    public function prepareButtonData($modelData): array
    {
        return []; // No buttons for customer messages
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
                        "Hello {{1}},\n\n" .
                        "This is a friendly reminder that your rental period is ending soon.\n\n" .
                        "🧾 Booking Reference: {{2}}\n\n" .
                        "🚗 Vehicle Details:\n" .
                        "- Make: {{3}}\n" .
                        "- Model: {{4}}\n" .
                        "- License Plate: {{5}}\n\n" .
                        "📅 Rental End: {{6}} at {{7}} (in approximately {{8}})\n\n" .
                        "📍 Drop-off Location: {{9}}\n\n" .
                        "If you need to extend your rental, please contact our customer service as soon as possible.\n\n" .
                        "Thank you for choosing our service!",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "RNT-202504-0001", // {{2}} Reference
                                "Mercedes-Benz", // {{3}} Vehicle name
                                "S-Class", // {{4}} Vehicle model
                                "ABC123", // {{5}} License plate
                                "2025-05-15", // {{6}} End date
                                "12:00", // {{7}} End time
                                "2 hours", // {{8}} Time remaining
                                "Dubai Airport Terminal 3", // {{9}} Drop-off address
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
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

    public function getGroup(): string
    {
        return "customer";
    }

    public function phoneNumbers($data)
    {
        /** @var Rent $data */
        return $data->client_phone;
    }
}
