<?php

namespace App\Observers;

use App\Enums\Shipping\ShippingStatus;
use App\Models\Shipping;
use App\Services\WhatsApp\Admin\Shipping\ASDeliveredHandler;
use App\Services\WhatsApp\Admin\Shipping\ASNewHandler;

class ShippingObserver extends NotificationObserver
{
    /**
     * Handle the Shipping "created" event.
     */
    public function created(Shipping $shipping): void
    {
        $this->sendAndSave(ASNewHandler::class, $shipping);
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
    }
}
