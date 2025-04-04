<?php

namespace App\Services\WhatsApp\Abstract;

interface WhatsAppNotificationInterface
{
    public function phoneNumbers($data);

    public function isEnabled(): bool;
}
