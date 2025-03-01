<?php

namespace App\Providers;

use App\Models\ShippingItem;
use App\Observers\ShippingItemObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ShippingItem::observe(ShippingItemObserver::class);
    }
}
