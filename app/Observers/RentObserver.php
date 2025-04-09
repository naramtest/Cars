<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Models\Rent;
use App\Services\WhatsApp\Admin\Rent\ARNewHandler;
use App\Services\WhatsApp\Customer\Rent\CRNewHandler;

class RentObserver extends NotificationObserver
{
    public function created(Rent $rent): void
    {
        $this->sendAndSave(ARNewHandler::class, $rent);

        if ($rent->status === ReservationStatus::Confirmed) {
            $this->sendAndSave(CRNewHandler::class, $rent);
        }
    }

    public function updated(Rent $rent): void
    {
        // Check if status was changed from pending to confirmed
        if (
            $rent->isDirty("status") &&
            $rent->getOriginal("status") === ReservationStatus::Pending &&
            $rent->status === ReservationStatus::Confirmed
        ) {
            // Send notification to driver if vehicle has a driver assigned
            if ($rent->vehicle && $rent->vehicle->driver) {
                $this->sendAndSave(CRNewHandler::class, $rent);
            }
        }
    }
}
