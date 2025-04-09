<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Rent;
use App\Models\Shipping;
use App\Models\ShippingItem;
use App\Observers\BookingObserver;
use App\Observers\RentObserver;
use App\Observers\ShippingItemObserver;
use App\Observers\ShippingObserver;
use Illuminate\Support\ServiceProvider;

class ObserverProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        ShippingItem::observe(ShippingItemObserver::class);
        Booking::observe(BookingObserver::class);
        Rent::observe(RentObserver::class);
        Shipping::observe(ShippingObserver::class);
    }
}
