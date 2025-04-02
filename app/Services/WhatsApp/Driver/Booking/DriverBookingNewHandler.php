<?php

namespace App\Services\WhatsApp\Driver\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\AbstractNotificationHandler;

class DriverBookingNewHandler extends AbstractNotificationHandler
{
    /** @var Booking $modelData */
    public function prepareBodyData($modelData): array
    {
        $driver = $modelData->driver;
        $vehicle = $modelData->vehicle;

        return [
            ["type" => "text", "text" => $driver->name], // 1 - Driver's name
            ["type" => "text", "text" => $modelData->reference], // 2 - Booking ref
            ["type" => "text", "text" => $$modelData->client_name], // 3 - Customer name
            ["type" => "text", "text" => $modelData->client_phone], // 4 - Customer phone
            ["type" => "text", "text" => $vehicle->model], // 5 - Vehicle model
            ["type" => "text", "text" => $vehicle->license_plate], // 6 - Vehicle plate
            [
                "type" => "text",
                "text" => $modelData->start_datetime->format("Y-m-d"),
            ], // 7 - Pickup date
            [
                "type" => "text",
                "text" => $modelData->end_datetime->format("H:i"),
            ], // 8 - Pickup time
            ["type" => "text", "text" => $modelData->pickup_address], // 9 - Pickup location
            ["type" => "text", "text" => $modelData->destination_address], // 10 - Dropoff location
            ["type" => "text", "text" => $modelData->notes ?? ""], // 11 - Optional instructions
        ];
    }

    public function prepareButtonData($modelData): array
    {
        // TODO: Implement prepareButtonData() method.
    }

    protected function getGroup(): string
    {
        return "driver";
    }
}
