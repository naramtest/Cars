<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\ShippingItem;
use App\Observers\BookingObserver;
use App\Observers\ShippingItemObserver;
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
    }
}
