<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Enums\ReservationStatus;
use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Services\WhatsApp\Admin\Booking\ABNewHandler;
use App\Services\WhatsApp\Customer\Booking\CBNewHandler;
use App\Services\WhatsApp\Driver\Booking\DBNewHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\Client\ConnectionException;
use Netflie\WhatsAppCloudApi\Response\ResponseException;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    public function afterCreate(): void
    {
        //TODO: convert from this to an event and listener
        /** @var Booking $booking */
        $booking = $this->record;
        $service = app(WhatsAppNotificationService::class);
        try {
            $service->sendAndSave(ABNewHandler::class, $booking);

            if ($booking->status === ReservationStatus::Confirmed) {
                $service->sendAndSave(DBNewHandler::class, $booking);
                $service->sendAndSave(CBNewHandler::class, $booking);
            }
        } catch (ConnectionException | ResponseException | \Exception $e) {
            logger()->error(
                "Failed to send  notification: " . $e->getMessage()
            );
        }
    }
}
