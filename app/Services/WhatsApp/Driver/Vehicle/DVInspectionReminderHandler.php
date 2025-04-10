<?php

namespace App\Services\WhatsApp\Driver\Vehicle;

use App\Filament\Resources\InspectionResource;
use App\Models\Vehicle;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class DVInspectionReminderHandler extends WhatsAppAbstractHandler
{
    /** @var Vehicle $modelData */
    public function prepareBodyData($modelData): array
    {
        $lastInspection = $modelData->inspections()->latest()->first();
        $lastInspectionDate = $lastInspection
            ? $lastInspection->inspection_date->format("Y-m-d")
            : "Never";
        $nextInspectionDate = $modelData->next_inspection_date
            ? $modelData->next_inspection_date->format("Y-m-d")
            : "Not set";
        $daysUntil = $modelData->days_until_next_inspection ?? 0;

        return $this->formatBodyParameters([
            $modelData->driver->full_name, // 1 - Driver name
            $modelData->name, // 2 - Vehicle name
            $modelData->model, // 3 - Vehicle model
            $modelData->license_plate, // 4 - License plate
            $lastInspectionDate, // 5 - Last inspection date
            $nextInspectionDate, // 6 - Next inspection date
            (string) $daysUntil, // 7 - Days until inspection
        ]);
    }

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
                        'Hi {{1}},\n\n' .
                        '⚠️ *Vehicle Inspection Reminder* ⚠️\n\n' .
                        'Your vehicle is due for inspection soon:\n\n' .
                        '🚗 Vehicle: *{{2}} ({{3}})*\n' .
                        '🔢 License Plate: *{{4}}*\n\n' .
                        '📅 Last Inspection: {{5}}\n' .
                        '📅 Next Inspection Due: *{{6}}*\n' .
                        '⏰ Days Remaining: *{{7}}*\n\n' .
                        "Please ensure the vehicle is ready for inspection to maintain compliance and safety standards.",
                    "example" => [
                        "body_text" => [
                            [
                                "Ahmed Hassan", // {{1}} Driver name
                                "Toyota Camry", // {{2}} Vehicle name
                                "2021", // {{3}} Vehicle model
                                "ABC-1234", // {{4}} License plate
                                "2024-10-01", // {{5}} Last inspection date
                                "2025-04-01", // {{6}} Next inspection date
                                "7", // {{7}} Days until inspection
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "Add Inspection",
                            "url" => templateUrl(
                                InspectionResource::getUrl("create")
                            ),
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
        /** @var Vehicle $data */
        return $data->driver->phone_number;
    }
}
