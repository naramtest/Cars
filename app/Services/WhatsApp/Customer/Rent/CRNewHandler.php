<?php

namespace App\Services\WhatsApp\Customer\Rent;

use App\Models\Rent;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CRNewHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for customer rent confirmation notification
     *
     * @param Rent $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $vehicle = $modelData->vehicle;

        return $this->formatBodyParameters([
            $modelData->client_name, // 1 - Customer name
            $modelData->reference_number, // 2 - Rent reference number
            $vehicle->name, // 3 - Vehicle name
            $vehicle->model, // 4 - Vehicle model
            $vehicle->license_plate, // 5 - Vehicle license plate
            $modelData->rental_start_date->format("Y-m-d"), // 6 - Start date
            $modelData->rental_start_date->format("H:i"), // 7 - Start time
            $modelData->rental_end_date->format("Y-m-d"), // 8 - End date
            $modelData->rental_end_date->format("H:i"), // 9 - End time
            $modelData->pickup_address, // 10 - Pickup address
            $modelData->drop_off_address, // 11 - Drop off address
            $modelData->formatted_total_price, // 12 - Total price
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
                        "Your rental reservation has been confirmed!\n\n" .
                        "🧾 Booking Reference: {{2}}\n\n" .
                        "🚗 Vehicle Details:\n" .
                        "- Make: {{3}}\n" .
                        "- Model: {{4}}\n" .
                        "- License Plate: {{5}}\n\n" .
                        "📅 Rental Period:\n" .
                        "- Start: {{6}} at {{7}}\n" .
                        "- End: {{8}} at {{9}}\n" .
                        "📍 Locations:\n" .
                        "- Pickup: {{10}}\n" .
                        "- Drop-off: {{11}}\n\n" .
                        "💰 Total Price: {{12}}\n\n" .
                        "If you have any questions, please contact our customer service.\n\n" .
                        "Thank you for choosing our service!",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "RNT-202504-0001", // {{2}} Reference
                                "Mercedes-Benz", // {{3}} Vehicle name
                                "S-Class", // {{4}} Vehicle model
                                "ABC123", // {{5}} License plate
                                "2025-05-10", // {{6}} Start date
                                "14:00", // {{7}} Start time
                                "2025-05-15", // {{8}} End date
                                "12:00", // {{9}} End time
                                "Dubai Marina", // {{10}} Pickup address
                                "Dubai Airport Terminal 3", // {{11}} Drop-off address
                                "AED 2,500.00", // {{12}} Total price
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
