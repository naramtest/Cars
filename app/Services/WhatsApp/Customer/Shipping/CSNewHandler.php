<?php

namespace App\Services\WhatsApp\Customer\Shipping;

use App\Models\Shipping;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CSNewHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for customer shipping notification
     *
     * @param Shipping $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $pickupTime = $modelData->pick_up_at
            ? $modelData->pick_up_at->format("Y-m-d H:i")
            : "As soon as possible";

        return $this->formatBodyParameters([
            $modelData->client_name, // 1 - Customer name
            $modelData->reference_number ?: "Undefined", // 2 - Shipping reference
            $modelData->pickup_address, // 3 - Pickup address
            $modelData->delivery_address, // 4 - Delivery address
            $pickupTime, // 5 - Scheduled pickup time
            $modelData->driver
                ? $modelData->driver->full_name
                : "To be assigned", // 6 - Driver name
        ]);
    }

    public function prepareButtonData($modelData): array
    {
        return []; // No buttons for customer shipping notifications
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
                        "Your shipping order has been confirmed! 📦\n\n" .
                        "🧾 Reference Number: {{2}}\n\n" .
                        "📍 Pickup Address: {{3}}\n\n" .
                        "🏁 Delivery Address: {{4}}\n\n" .
                        "⏰ Scheduled Pickup: {{5}}\n\n" .
                        "👨‍✈️ Driver: {{6}}\n\n" .
                        "We'll update you when your package is picked up and delivered. If you have any questions, please don't hesitate to contact our support team.",
                    "example" => [
                        "body_text" => [
                            [
                                "John Smith", // {{1}} Customer name
                                "SHP-202504-0001", // {{2}} Reference number
                                "Dubai Marina, Building 7", // {{3}} Pickup address
                                "Dubai Airport Terminal 3", // {{4}} Delivery address
                                "2025-04-15 14:00", // {{5}} Scheduled pickup
                                "Ahmed Hassan", // {{6}} Driver name
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
        /** @var Shipping $data */
        return $data->client_phone;
    }

    public function isEnabled(): bool
    {
        return true;
    }
}
