<?php

namespace App\Services\WhatsApp\Traits;

use App\Models\Booking;

trait BookingNotificationData
{
    /**
     * Standard body data preparation for booking-related handlers
     *
     * @param Booking $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $driver = $modelData->driver;
        $vehicle = $modelData->vehicle;

        return $this->formatBodyParameters([
            $driver->full_name ?? " ", // 1 - Driver's name
            $modelData->reference_number ?: "Undefined", // 2 - Booking ref
            $modelData->getCustomer()->name, // 3 - Customer name
            $modelData->getCustomer()->phone_number, // 4 - Customer phone
            $vehicle->name, // 5 - Vehicle model
            $vehicle->license_plate, // 6 - Vehicle plate
            $modelData->start_datetime->format("Y-m-d"), // 7 - Pickup date
            $modelData->start_datetime->format("H:i"), // 8 - Pickup time
            $modelData->pickup_address, // 9 - Pickup location
            $modelData->destination_address ?: "Undefined", // 10 - Dropoff location
            $modelData->notes ?? "nothing", // 11 - Optional instructions
        ]);
    }
}
