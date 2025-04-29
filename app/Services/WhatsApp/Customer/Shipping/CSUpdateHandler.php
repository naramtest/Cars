<?php

namespace App\Services\WhatsApp\Customer\Shipping;

use App\Models\Shipping;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CSUpdateHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for customer shipping update notification
     *
     * @param Shipping $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        return $this->formatBodyParameters([
            $modelData->getCustomer()->name, // 1 - Customer name
            $modelData->reference_number ?: "Undefined", // 2 - Shipping reference
            $modelData->driver ? $modelData->driver->full_name : "Not assigned", // 3 - Current driver
            $modelData->pickup_address, // 4 - Current pickup address
            $modelData->delivery_address, // 5 - Current delivery address
            $modelData->pick_up_at
                ? $modelData->pick_up_at->format("Y-m-d H:i")
                : "Not scheduled", // 6 - Current pickup time
            $modelData->notes ?: "No additional notes", // 7 - Notes
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
                        "text" => $driver ? $driver->id : null,
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
                        "Hello {{1}},\n\n" .
                        "Your shipping order with reference number {{2}} has been updated. Please review the current details below:\n\n" .
                        "👨‍✈️ Driver: {{3}}\n" .
                        "📍 Pickup Location: {{4}}\n" .
                        "🏁 Delivery Location: {{5}}\n" .
                        "⏰ Scheduled Pickup: {{6}}\n" .
                        "📝 Notes: {{7}}\n\n" .
                        "If you have any questions about these changes, please contact our support team.",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "SHP-202504-0001", // {{2}} Reference number
                                "Ahmed Hassan", // {{3}} Current driver
                                "Dubai Marina, Building 7", // {{4}} Pickup address
                                "Dubai Airport Terminal 3", // {{5}} Delivery address
                                "2025-04-15 14:00", // {{6}} Scheduled pickup
                                "Fragile items, handle with care", // {{7}} Notes
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

    public function getGroup(): string
    {
        return "customer";
    }

    public function phoneNumbers($data)
    {
        /** @var Shipping $data */
        return $data->getCustomer()->phone_number;
    }
}
