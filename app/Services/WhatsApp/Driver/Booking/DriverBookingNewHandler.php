<?php

namespace App\Services\WhatsApp\Driver\Booking;

use App\Services\WhatsApp\AbstractNotificationHandler;

class DriverBookingNewHandler extends AbstractNotificationHandler
{
    public function prepareData(array $modelData): array {}

    protected function getGroup(): string
    {
        return "driver";
    }
}
