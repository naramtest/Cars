<?php

namespace App\Providers;

use App\Services\Payments\PaymentManager;
use App\Services\Payments\Providers\Stripe\StripeElementsProvider;
use App\Services\Payments\Providers\Stripe\StripePaymentLinksProvider;
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
        $this->app->singleton(StripeElementsProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
