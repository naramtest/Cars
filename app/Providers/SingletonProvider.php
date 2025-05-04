<?php

namespace App\Providers;

use App\Services\Payments\PaymentManager;
use App\Services\Payments\Providers\StripePaymentLinksProvider;
use Illuminate\Support\ServiceProvider;

class SingletonProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class);
        $this->app->singleton(StripePaymentLinksProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
