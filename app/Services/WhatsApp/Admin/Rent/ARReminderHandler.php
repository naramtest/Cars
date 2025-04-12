<?php

namespace App\Services\WhatsApp\Admin\Rent;

use App\Filament\Resources\RentResource;
use App\Models\Rent;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Traits\HasAdminPhoneNumbers;

class ARReminderHandler extends WhatsAppAbstractHandler
{
    use HasAdminPhoneNumbers;
    
    // Define reminder types as constants
    const TYPE_START = "start";
    const TYPE_END = "end";

    private string $reminderType;

    public function __construct(string $reminderType = self::TYPE_START)
    {
        $this->reminderType = $reminderType;
    }

    /**
     * Prepares body data for rent reminder notification
     *
     * @param Rent $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $vehicle = $modelData->vehicle;

        // Determine which date to use based on reminder type
        $relevantDate =
            $this->reminderType === self::TYPE_START
                ? $modelData->rental_start_date
                : $modelData->rental_end_date;

        // Calculate how many hours until the relevant event
        $hoursRemaining = now()->diffInHours($relevantDate, false);
        $timeRemaining =
            $hoursRemaining > 1 ? "$hoursRemaining hours" : "1 hour";

        // Determine title and message based on type
        $actionTitle =
            $this->reminderType === self::TYPE_START ? "Start" : "End";

        return $this->formatBodyParameters([
            $modelData->reference_number ?: "Undefined", // 1 - Rent reference
            $modelData->client_name, // 2 - Customer name
            $modelData->client_phone, // 3 - Customer phone
            $vehicle->name, // 4 - Vehicle name
            $vehicle->model, // 5 - Vehicle model
            $vehicle->license_plate, // 6 - Vehicle plate
            $relevantDate->format("Y-m-d"), // 7 - Relevant date
            $relevantDate->format("H:i"), // 8 - Relevant time
            $timeRemaining, // 9 - Time remaining
            $this->reminderType, // 10 - Type of reminder (start/end)
            $actionTitle, // 11 - Action title (Start/End)
            $modelData->pickup_address, // 12 - Pickup address
            $modelData->drop_off_address, // 13 - Drop off address
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
                        "⏰ REMINDER: Upcoming Rental {{11}} ⏰\n\n" .
                        "Reference: {{1}}\n" .
                        "Client: {{2}} ({{3}})\n\n" .
                        "Vehicle Information:\n" .
                        "- Name: {{4}}\n" .
                        "- Model: {{5}}\n" .
                        "- License Plate: {{6}}\n\n" .
                        "Scheduled {{10}}: {{7}} at {{8}} (in approximately {{9}})\n\n" .
                        "Locations:\n" .
                        "- Pickup: {{12}}\n" .
                        "- Drop-off: {{13}}\n\n" .
                        "🚫 This is an automated reminder. Please do not reply.",
                    "example" => [
                        "body_text" => [
                            [
                                "RNT-202504-0001", // {{1}} Reference
                                "John Smith", // {{2}} Client name
                                "+971550000000", // {{3}} Client phone
                                "Mercedes-Benz", // {{4}} Vehicle name
                                "S-Class", // {{5}} Vehicle model
                                "ABC123", // {{6}} License plate
                                "2025-05-10", // {{7}} Date
                                "14:00", // {{8}} Time
                                "2 hours", // {{9}} Time remaining
                                "start", // {{10}} Type
                                "Start", // {{11}} Title
                                "Dubai Marina", // {{12}} Pickup address
                                "Dubai Airport Terminal 3", // {{13}} Drop-off address
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
}
