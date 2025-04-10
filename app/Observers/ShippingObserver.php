<?php

namespace App\Observers;

use App\Enums\Shipping\ShippingStatus;
use App\Models\Shipping;
use App\Services\WhatsApp\Admin\Shipping\ASDeliveredHandler;
use App\Services\WhatsApp\Admin\Shipping\ASNewHandler;
use App\Services\WhatsApp\Driver\Shipping\DSNewHandler;

class ShippingObserver extends NotificationObserver
{
    /**
     * Handle the Shipping "created" event.
     */
    public function created(Shipping $shipping): void
    {
        $this->sendAndSave(ASNewHandler::class, $shipping);
        // If shipping is created with Confirmed status and has a driver
        if (
            $shipping->status === ShippingStatus::Confirmed &&
            $shipping->driver_id
        ) {
            $this->sendAndSave(DSNewHandler::class, $shipping);
        }
    }

    public function updated(Shipping $shipping): void
    {
        // Check if status was changed to delivered
        if (
            $shipping->isDirty("status") &&
            $shipping->status === ShippingStatus::Delivered &&
            $shipping->getOriginal("status") !== ShippingStatus::Delivered
        ) {
            // If delivered_at is not set, set it to now
            if (!$shipping->delivered_at) {
                $shipping->delivered_at = now();
                $shipping->save();
            }

            $this->sendAndSave(ASDeliveredHandler::class, $shipping);
        }

        // Check if status was changed from pending to confirmed
        if (
            $shipping->isDirty("status") &&
            $shipping->status === ShippingStatus::Confirmed &&
            $shipping->getOriginal("status") === ShippingStatus::Pending &&
            $shipping->driver_id
        ) {
            $this->sendAndSave(DSNewHandler::class, $shipping);
        }
    }
}
