<?php

namespace App\Listeners;

use App\Enums\ReservationStatus;
use App\Events\BookingCreated;
use App\Services\WhatsApp\Admin\Booking\ABNewHandler;
use App\Services\WhatsApp\Customer\Booking\CBNewHandler;
use App\Services\WhatsApp\Driver\Booking\DBNewHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class SendBookingNotifications implements ShouldQueue
{
    public int $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct(
        protected WhatsAppNotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        logger("naram");
        $booking = $event->booking;

        try {
            // Always send admin notification
            $this->notificationService->sendAndSave(
                ABNewHandler::class,
                $booking
            );

            // Only send driver and customer notifications if status is confirmed
            if ($booking->status === ReservationStatus::Confirmed) {
                if ($booking->driver_id) {
                    $this->notificationService->sendAndSave(
                        DBNewHandler::class,
                        $booking
                    );
                }

                $this->notificationService->sendAndSave(
                    CBNewHandler::class,
                    $booking
                );
            }
        } catch (\Exception $e) {
            Log::error("Failed to send booking notification", [
                "booking_id" => $booking->id,
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
        }
    }
}
