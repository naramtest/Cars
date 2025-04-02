<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Models\Booking;
use App\Services\WhatsApp\Driver\Booking\DriverBookingNewHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;

class BookingObserver
{
    public function __construct(
        protected WhatsAppNotificationService $notificationService
    ) {}

    public function created(Booking $booking): void
    {
        if ($booking->status === ReservationStatus::Confirmed) {
            $result = $this->notificationService->send(
                DriverBookingNewHandler::class,
                $booking
            );
            dd($result);
        }
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Check if status was changed from pending to confirmed
        if (
            $booking->isDirty("status") &&
            $booking->getOriginal("status") === ReservationStatus::Pending &&
            $booking->status === ReservationStatus::Confirmed
        ) {
            $result = $this->notificationService->send(
                DriverBookingNewHandler::class,
                $booking
            );
            dd($result);
        }
    }
}
