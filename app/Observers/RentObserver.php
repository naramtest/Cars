<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Models\Rent;
use App\Services\WhatsApp\Customer\Rent\CRNewHandler;
use App\Services\WhatsApp\Customer\Rent\CRUpdateHandler;

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
            // Send notification to customer
            $this->sendAndSave(CRNewHandler::class, $rent);
        }

        // Send update notification if important fields were changed
        if ($this->shouldSendUpdateNotification($rent)) {
            $this->sendAndSave(CRUpdateHandler::class, $rent);
        }
    }

    /**
     * Determines if a rent update notification should be sent
     */
    protected function shouldSendUpdateNotification(Rent $rent): bool
    {
        // Only send updates for confirmed/active rentals
        if (
            !in_array($rent->status, [
                ReservationStatus::Confirmed,
                ReservationStatus::Active,
            ])
        ) {
            return false;
        }

        // Check if important fields have changed
        $watchedFields = [
            "vehicle_id",
            "rental_start_date",
            "rental_end_date",
            "pickup_address",
            "drop_off_address",
            "total_price",
        ];

        foreach ($watchedFields as $field) {
            if ($rent->isDirty($field)) {
                return true;
            }
        }

        return false;
    }
}
