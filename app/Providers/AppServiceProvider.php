<?php

namespace App\Providers;

use App\Settings\InfoSettings;
use Auth;
use Illuminate\Support\ServiceProvider;
use LogViewer;
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

        LogViewer::auth(function ($request) {
            return Auth::user()->hasRole("super_admin");
        });
    }
}
