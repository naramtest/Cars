<?php

namespace App\Observers;

use App\Models\Shipping;
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
}
