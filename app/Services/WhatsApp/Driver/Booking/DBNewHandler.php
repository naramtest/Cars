<?php

namespace App\Services\WhatsApp\Driver\Booking;

use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Services\WhatsApp\Traits\BookingNotificationData;

class DBNewHandler extends WhatsAppAbstractHandler
{
    use BookingNotificationData;

    /** @var Booking $modelData */

    public function prepareButtonData($modelData): array
    {
        return [];
    }

    public function getGroup(): string
    {
        return "driver";
    }

    public function phoneNumbers($data)
    {
        /** @var  Booking $data */
        return $data->driver->phone_number;
    }

    public function isEnabled(): bool
    {
        // TODO: Implement isEnabled() method.
        return true;
    }

    public function facebookTemplateData(): array
    {
        // TODO: Implement getTemplate() method.
    }
}
