<?php

namespace App\Providers;

use App\Settings\InfoSettings;
use Illuminate\Support\ServiceProvider;
use View;

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
        View::composer(["components.layout.*"], function (
            $view,
            InfoSettings $infoSettings
        ) {
            $view->with("info", $infoSettings);
        });
    }
}
