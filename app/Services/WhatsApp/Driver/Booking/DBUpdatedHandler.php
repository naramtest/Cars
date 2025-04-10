<?php

namespace App\Services\WhatsApp\Driver\Booking;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class DBUpdatedHandler extends WhatsAppAbstractHandler
{
    /** @var Booking $modelData */
    public function prepareBodyData($modelData): array
    {
        $changes = $modelData->getChanges(); // Only changed attributes
        $original = $modelData->getOriginal(); // Original data

        $messages = [];

        if (isset($changes["start_datetime"])) {
            $messages[] =
                "📅 *Pickup Date/Time changed:* " .
                optional($original["start_datetime"])->format("Y-m-d H:i") .
                " ➡️ " .
                $modelData->start_datetime->format("Y-m-d H:i");
        }

        if (isset($changes["end_datetime"])) {
            $messages[] =
                "📅 *Drop off Date/Time changed:* " .
                optional($original["end_datetime"])->format("Y-m-d H:i") .
                " ➡️ " .
                $modelData->end_datetime->format("Y-m-d H:i");
        }

        if (isset($changes["pickup_address"])) {
            $messages[] =
                "📍 *Pickup Location changed:* " .
                $original["pickup_address"] .
                " ➡️ " .
                $modelData->pickup_address;
        }

        if (isset($changes["destination_address"])) {
            $messages[] =
                "🏁 *Dropoff Location changed:* " .
                $original["destination_address"] .
                " ➡️ " .
                $modelData->destination_address;
        }

        if (isset($changes["notes"])) {
            $messages[] = "📝 *Notes updated:* " . $modelData->notes;
        }

        // Add more fields as needed...

        return $this->formatBodyParameters([
            $modelData->driver->full_name ?? "Driver", // 1 - Driver name
            $modelData->reference_number ?? "N/A", // 2 - Booking ref
            implode("\n", $messages) ?: "No major changes found.", // 3 - Summary of changes
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
                        "text" => $modelData->reference_number ?? "N/A",
                    ],
                ],
            ],
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 1,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $modelData->id,
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
                        "Hi {{1}}, this is a quick update regarding booking *{{2}}*.\n\n{{3}}\n\nPlease review the changes and proceed accordingly.",
                    "example" => [
                        "body_text" => [
                            [
                                "John Doe",
                                "BOK-202504-0001",
                                "📅 Pickup Date/Time changed: 2025-04-05 10:00 ➡️ 2025-04-06 12:00\n📍 Pickup Location changed: Downtown ➡️ Uptown",
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
                        [
                            "type" => "URL",
                            "text" => "View Booking Details",
                            "url" => templateUrl(
                                BookingResource::getUrl() . "/{{1}}"
                            ),
                            "example" => ["1"],
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
        return $data->driver->phone_number;
    }
}
