<?php

namespace App\Observers;

use App\Models\ShippingItem;

class ShippingItemObserver
{
    /**
     * Handle the ShippingItem "created" event.
     */
    public function created(ShippingItem $shippingItem): void
    {
        $this->updateShippingTotalWeight($shippingItem);
    }

    /**
     * Update the total weight of the associated shipping
     */
    protected function updateShippingTotalWeight(
        ShippingItem $shippingItem
    ): void {
        if ($shippingItem->shipping) {
            $shippingItem->shipping->recalculateTotalWeight();
        }
    }

    /**
     * Handle the ShippingItem "updated" event.
     */
    public function updated(ShippingItem $shippingItem): void
    {
        $this->updateShippingTotalWeight($shippingItem);
    }

    /**
     * Handle the ShippingItem "deleted" event.
     */
    public function deleted(ShippingItem $shippingItem): void
    {
        $this->updateShippingTotalWeight($shippingItem);
    }
}
