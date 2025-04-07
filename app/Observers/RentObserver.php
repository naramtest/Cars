<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Models\Rent;
use App\Services\WhatsApp\Admin\Rent\ARNewHandler;
use App\Services\WhatsApp\Customer\Rent\CRNewHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Illuminate\Http\Client\ConnectionException;
use Netflie\WhatsAppCloudApi\Response\ResponseException;

class RentObserver
{
    public function __construct(
        protected WhatsAppNotificationService $notificationService
    ) {}

    public function created(Rent $rent): void
    {
        $this->sendAndSave(ARNewHandler::class, $rent);

        if ($rent->status === ReservationStatus::Confirmed) {
            $this->sendAndSave(CRNewHandler::class, $rent);
        }
    }

    private function sendAndSave(string $class, Rent $rent): void
    {
        try {
            $this->notificationService->sendAndSave($class, $rent);
        } catch (ConnectionException | ResponseException $e) {
            logger()->error(
                "Failed to send rent notification: " . $e->getMessage(),
                [
                    "rent_id" => $rent->id,
                    "handler" => $class,
                ]
            );
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
