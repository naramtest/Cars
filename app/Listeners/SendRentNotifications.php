<?php

namespace App\Listeners;

use App\Enums\ReservationStatus;
use App\Events\RentCreated;
use App\Services\WhatsApp\Admin\Rent\ARNewHandler;
use App\Services\WhatsApp\Customer\Rent\CRNewHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class SendRentNotifications implements ShouldQueue
{
    public int $tries = 3;

    public function __construct(
        protected WhatsAppNotificationService $notificationService
    ) {}

    public function handle(RentCreated $event): void
    {
        $rent = $event->rent;

        try {
            // Always send admin notification
            $this->notificationService->sendAndSave(ARNewHandler::class, $rent);

            if ($rent->status === ReservationStatus::Confirmed) {
                $this->notificationService->sendAndSave(
                    CRNewHandler::class,
                    $rent
                );
            }
        } catch (Exception $e) {
            Log::error("Failed to send booking notification", [
                "booking_id" => $rent->id,
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
        }
    }
}
