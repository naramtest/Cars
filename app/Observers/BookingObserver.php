<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Models\Booking;
use App\Services\WhatsApp\Customer\Booking\CBNewHandler;
use App\Services\WhatsApp\Customer\Booking\CBUpdateHandler;
use App\Services\WhatsApp\Driver\Booking\DBNewHandler;
use App\Services\WhatsApp\Driver\Booking\DBUpdatedHandler;

class BookingObserver extends NotificationObserver
{
    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Check if status was changed from pending to confirmed
        if (
            $booking->check(
                ReservationStatus::Confirmed,
                ReservationStatus::Pending
            )
        ) {
            $this->sendAndSave(DBNewHandler::class, $booking);
            $this->sendAndSave(CBNewHandler::class, $booking);
        }

        // Send DBUpdatedHandler if other fields were changed
        if ($this->shouldSendUpdateNotification($booking)) {
            $this->sendAndSave(DBUpdatedHandler::class, $booking, true);

            // Also send to customer
            $this->sendAndSave(CBUpdateHandler::class, $booking, true);
        }
    }

    protected function shouldSendUpdateNotification(Booking $booking): bool
    {
        if (
            $booking->status !== ReservationStatus::Confirmed and
            $booking->status !== ReservationStatus::Active
        ) {
            return false;
        }
        $watchedFields = [
            "vehicle_id",
            "driver_id",
            "start_datetime",
            "end_datetime",
            "pickup_address",
            "destination_address",
            "total_price",
        ];

        foreach ($watchedFields as $field) {
            if ($booking->isDirty($field)) {
                return true;
            }
        }

        return false;
    }
}
