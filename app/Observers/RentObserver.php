<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Models\Rent;
use App\Services\WhatsApp\Customer\Rent\CRNewHandler;

class RentObserver extends NotificationObserver
{
    public function updated(Rent $rent): void
    {
        // Check if status was changed from pending to confirmed
        if (
            $rent->check(
                ReservationStatus::Confirmed,
                ReservationStatus::Pending
            )
        ) {
            // Send notification to driver if vehicle has a driver assigned
            if ($rent->vehicle && $rent->vehicle->driver) {
                $this->sendAndSave(CRNewHandler::class, $rent);
            }
        }
    }
}
