<?php

namespace App\Services\WhatsApp\Admin\Vehicle;

use App\Filament\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Traits\HasAdminPhoneNumbers;

class VehicleInspectionReminderHandler extends WhatsAppAbstractHandler
{
    use HasAdminPhoneNumbers;

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
            $modelData->name, // 1 - Vehicle name
            $modelData->model, // 2 - Vehicle model
            $modelData->license_plate, // 3 - License plate
            $lastInspectionDate, // 4 - Last inspection date
            $nextInspectionDate, // 5 - Next inspection date
            (string) $daysUntil, // 6 - Days until inspection
            $modelData->driver
                ? $modelData->driver->full_name
                : "No driver assigned", // 7 - Driver name
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
                        "📢 *Vehicle Inspection Reminder*\n\nThe following vehicle is due for inspection soon:\n\n🚗 Vehicle: *{{1}} ({{2}})*\n🔢 License Plate: *{{3}}*\n\n📅 Last Inspection: {{4}}\n📅 Next Inspection Due: *{{5}}*\n⏰ Days Remaining: *{{6}}*\n👤 Assigned Driver: {{7}}\n\nPlease schedule an inspection as soon as possible to ensure vehicle compliance and safety.",
                    "example" => [
                        "body_text" => [
                            [
                                "Toyota Camry", // {{1}} Vehicle name
                                "2021", // {{2}} Vehicle model
                                "ABC-1234", // {{3}} License plate
                                "2024-10-01", // {{4}} Last inspection date
                                "2025-04-01", // {{5}} Next inspection date
                                "7", // {{6}} Days until inspection
                                "John Doe", // {{7}} Driver name
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "View Vehicle Details",
                            "url" => templateUrl(
                                VehicleResource::getUrl() . "/{{1}}"
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
