<?php

namespace App\Services\WhatsApp\Driver\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class DBNewHandler extends WhatsAppAbstractHandler
{
    /** @var Booking $modelData */
    public function prepareBodyData($modelData): array
    {
        $driver = $modelData->driver;
        $vehicle = $modelData->vehicle;

        return $this->formatBodyParameters([
            $driver->full_name ?? " ", // 1 - Driver's name
            $modelData->reference_number ?: "Undefined", // 2 - Booking ref
            $modelData->client_name, // 3 - Customer name
            $modelData->client_phone, // 4 - Customer phone
            $vehicle->model, // 5 - Vehicle model
            $vehicle->license_plate, // 6 - Vehicle plate
            $modelData->start_datetime->format("Y-m-d"),
            // 7 - Pickup date
            $modelData->end_datetime->format("H:i"), // 8 - Pickup time
            $modelData->pickup_address, // 9 - Pickup location
            $modelData->destination_address ?: "Undefined", // 10 - Dropoff location
            $modelData->notes ?? "nothing", // 11 - Optional instructions
        ]);
    }

    public function prepareButtonData($modelData): array
    {
        return [];
    }

    public function getGroup(): string
    {
        return "driver";
    }

    public function phoneNumbers($data)
    {
        /** @var  Booking $data */
        return $data->driver->phone_number;
    }

    public function isEnabled(): bool
    {
        // TODO: Implement isEnabled() method.
        return true;
    }

    public function facebookTemplateData(): array
    {
        // TODO: Implement getTemplate() method.
    }
}
