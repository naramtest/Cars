<?php

namespace App\Services\WhatsApp\Driver\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\AbstractNotificationHandler;
use App\Settings\InfoSettings;

class DriverBookingNewHandler extends AbstractNotificationHandler
{
    /** @var Booking $modelData */
    public function prepareBodyData($modelData): array
    {
        $driver = $modelData->driver;
        $vehicle = $modelData->vehicle;

        return $this->formatBodyParameters([
            $driver->name ?? "Naram", // 1 - Driver's name
            "1", // 2 - Booking ref
            $modelData->client_name, // 3 - Customer name
            $modelData->client_phone, // 4 - Customer phone
            $vehicle->model, // 5 - Vehicle model
            $vehicle->license_plate, // 6 - Vehicle plate
            $modelData->start_datetime->format("Y-m-d"),
            // 7 - Pickup date
            $modelData->end_datetime->format("H:i"), // 8 - Pickup time
            $modelData->pickup_address, // 9 - Pickup location
            "home", // 10 - Dropoff location
            $modelData->notes ?? "nothing", // 11 - Optional instructions
        ]);
        //        [
        //            $driver->name ?? "", // 1 - Driver's name
        //            $modelData->reference_number ?? "1", // 2 - Booking ref
        //            $modelData->client_name, // 3 - Customer name
        //            $modelData->client_phone, // 4 - Customer phone
        //            $vehicle->model, // 5 - Vehicle model
        //            $vehicle->license_plate, // 6 - Vehicle plate
        //            $modelData->start_datetime->format("Y-m-d"),
        //            // 7 - Pickup date
        //            $modelData->end_datetime->format("H:i"), // 8 - Pickup time
        //            $modelData->pickup_address, // 9 - Pickup location
        //            $modelData->destination_address, // 10 - Dropoff location
        //            $modelData->notes ?? "", // 11 - Optional instructions
        //        ]
    }

    public function prepareButtonData($modelData): array
    {
        $number = app(InfoSettings::class)->support_whatsapp_number;
        $whatsappLink =
            "https://wa.me/" . preg_replace("/[^0-9]/", "", $number);
        return [
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 0,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $whatsappLink,
                    ],
                ],
            ],
        ];
    }

    protected function getGroup(): string
    {
        return "driver";
    }
}
