<?php

namespace App\Traits;

use App\Models\ModelNotification;

trait HasNotifications
{
    public function recordNotification(string $type, array $metadata = [])
    {
        return $this->notifications()->create([
            "notification_type" => $type,
            "sent_at" => now(),
            "metadata" => $metadata,
        ]);
    }

    public function notifications()
    {
        return $this->morphMany(ModelNotification::class, "notifiable");
    }

    public function hasNotificationBeenSent(string $type): bool
    {
        return $this->notifications()
            ->where("notification_type", $type)
            ->exists();
    }

    public function getLatestNotification(string $type)
    {
        return $this->notifications()
            ->where("notification_type", $type)
            ->latest("sent_at")
            ->first();
    }

    public function hasPaymentLinkNotification(): bool
    {
        return $this->notifications()
            ->where("notification_type", "like", "%customer_payment_link%")
            ->exists();
    }
}
