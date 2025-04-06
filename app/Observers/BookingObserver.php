<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Models\Booking;
use App\Services\WhatsApp\Admin\Booking\ABNewHandler;
use App\Services\WhatsApp\Driver\Booking\DBNewHandler;
use App\Services\WhatsApp\Driver\Booking\DBUpdatedHandler;
use App\Services\WhatsApp\HandlerResolver;
use App\Services\WhatsApp\WhatsAppNotificationService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Http\Client\ConnectionException;
use Netflie\WhatsAppCloudApi\Response\ResponseException;

class BookingObserver
{
    public function __construct(
        protected WhatsAppNotificationService $notificationService,
        protected DBNewHandler $newHandler,
        protected WhatsAppTemplateService $templateService
    ) {}

    public function created(Booking $booking): void
    {
        $this->sendAndSave(ABNewHandler::class, $booking);

        if ($booking->status === ReservationStatus::Confirmed) {
            $this->sendAndSave(DBNewHandler::class, $booking);
        }
    }

    private function sendAndSave(string $class, Booking $booking): void
    {
        try {
            $handler = HandlerResolver::resolve($class);
            $this->notificationService->sendAndSave($handler, $booking);
        } catch (ConnectionException | ResponseException $e) {
            logger($e->getMessage());
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
            $this->sendAndSave(DBNewHandler::class, $booking);
        }

        // Send DBUpdatedHandler if other fields were changed
        if ($this->shouldSendUpdateNotification($booking)) {
            $this->sendAndSave(DBUpdatedHandler::class, $booking);
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
            "start_datetime",
            "end_datetime",
            "pickup_address",
            "destination_address",
            "notes",
        ];

        foreach ($watchedFields as $field) {
            if ($booking->isDirty($field)) {
                return true;
            }
        }

        return false;
    }
}
