<?php

namespace App\Observers;

use App\Enums\Shipping\ShippingStatus;
use App\Models\Shipping;
use App\Services\WhatsApp\Admin\Shipping\ASDeliveredHandler;
use App\Services\WhatsApp\Customer\Shipping\CSDeliveredHandler;
use App\Services\WhatsApp\Customer\Shipping\CSNewHandler;
use App\Services\WhatsApp\Customer\Shipping\CSPickedUpHandler;
use App\Services\WhatsApp\Customer\Shipping\CSUpdateHandler;
use App\Services\WhatsApp\Driver\Shipping\DSDeliveryHandler;
use App\Services\WhatsApp\Driver\Shipping\DSNewHandler;

class ShippingObserver extends NotificationObserver
{
    public function updated(Shipping $shipping): void
    {
        // Check if status was changed to delivered
        if ($shipping->check(ShippingStatus::Delivered)) {
            if (!$shipping->delivered_at) {
                $shipping->delivered_at = now();
                $shipping->save();
            }

            $this->sendAndSave(ASDeliveredHandler::class, $shipping);
            $this->sendAndSave(CSDeliveredHandler::class, $shipping);
        }

        // Check if status was changed from pending to confirmed
        if (
            $shipping->check(ShippingStatus::Confirmed, ShippingStatus::Pending)
        ) {
            $this->sendAndSave(CSNewHandler::class, $shipping);
            if ($shipping->driver_id) {
                $this->sendAndSave(DSNewHandler::class, $shipping);
            }
        }

        // Handle status changing to Picked_Up
        if ($shipping->check(ShippingStatus::Picked_Up)) {
            $this->sendAndSave(CSPickedUpHandler::class, $shipping);
            if ($shipping->driver_id) {
                $this->sendAndSave(DSDeliveryHandler::class, $shipping);
            }
        }

        // Send update notification for changes to important fields
        if ($this->shouldSendUpdateNotification($shipping)) {
            $this->sendAndSave(CSUpdateHandler::class, $shipping, true);
        }
    }

    /**
     * Determines if a shipping update notification should be sent
     */
    protected function shouldSendUpdateNotification(Shipping $shipping): bool
    {
        // Only send updates for confirmed or pending shipping orders
        if ($shipping->status !== ShippingStatus::Confirmed) {
            return false;
        }

        // Check if important fields have changed
        $watchedFields = [
            "driver_id",
            "pick_up_at",
            "pickup_address",
            "delivery_address",
            "notes",
        ];

        foreach ($watchedFields as $field) {
            if ($shipping->isDirty($field)) {
                return true;
            }
        }

        return false;
    }
}
