<?php

namespace App\Providers;

use App\Services\WhatsApp\WhatsAppNotificationService;
use Illuminate\Support\ServiceProvider;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register WhatsAppCloudApi
        $this->app->singleton(WhatsAppCloudApi::class, function ($app) {
            return new WhatsAppCloudApi([
                "from_phone_number_id" => config("services.whatsapp.phone_id"),
                "access_token" => config("services.whatsapp.token"),
            ]);
        });

        // Register WhatsAppNotificationService
        $this->app->singleton(WhatsAppNotificationService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
