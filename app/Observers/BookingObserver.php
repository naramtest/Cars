<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Models\Booking;
use App\Services\WhatsApp\Driver\Booking\DBNewHandler;
use App\Services\WhatsApp\Driver\Booking\DBUpdatedHandler;
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
        if ($booking->status === ReservationStatus::Confirmed) {
            $this->driverAssignBookingNotification($booking);
        }
    }

    public function driverAssignBookingNotification(Booking $booking): void
    {
        try {
            $template = $this->templateService->resolveTemplate(
                $this->newHandler
            );
            $this->notificationService->send($this->newHandler, $booking);

            $booking->recordNotification($template->name);
        } catch (ConnectionException | ResponseException | \Exception $e) {
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
            $this->driverAssignBookingNotification($booking);
        }

        // Send DBUpdatedHandler if other fields were changed
        if ($this->shouldSendUpdateNotification($booking)) {
            $this->sendDriverUpdateNotification($booking);
        }
    }

    protected function shouldSendUpdateNotification(Booking $booking): bool
    {
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

    protected function sendDriverUpdateNotification(Booking $booking): void
    {
        try {
            $handler = app(DBUpdatedHandler::class);
            $template = $this->templateService->resolveTemplate($handler);
            $this->notificationService->send($handler, $booking);

            $booking->recordNotification($template->name);
        } catch (ConnectionException | ResponseException | \Exception $e) {
            logger($e->getMessage());
        }
    }
}
