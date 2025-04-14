<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\BookingChartsWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Outerweb\FilamentTranslatableFields\Filament\Plugins\FilamentTranslatableFieldsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id("admin")
            ->path("admin")
            ->login()
            ->sidebarWidth("md")
            ->viteTheme("resources/css/filament/admin/theme.css")
            ->databaseNotifications()
            ->colors([
                "primary" => Color::Amber,
            ])
            ->plugins([
                SpatieLaravelTranslatablePlugin::make()->defaultLocales([
                    "en",
                    "ar",
                ]),
                FilamentShieldPlugin::make(),
                FilamentEditProfilePlugin::make()
                    ->setNavigationGroup(__("dashboard.System Settings"))
                    ->shouldShowDeleteAccountForm(false)
                    ->setIcon("heroicon-o-user")
                    ->shouldShowSanctumTokens(false)
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(false),
            ])
            ->discoverResources(
                in: app_path("Filament/Resources"),
                for: "App\\Filament\\Resources"
            )
            ->discoverPages(
                in: app_path("Filament/Pages"),
                for: "App\\Filament\\Pages"
            )
            ->plugins([
                FilamentTranslatableFieldsPlugin::make()->supportedLocales([
                    "en" => "English",
                    "ar" => "Arabic",
                ]),
            ])
            ->pages([Pages\Dashboard::class])
            ->navigationGroups([
                NavigationGroup::make()->label(
                    fn(): string => __("dashboard.Operations")
                ),
                NavigationGroup::make()->label(
                    fn(): string => __("dashboard.Business Management")
                ),
                NavigationGroup::make()
                    ->label(fn(): string => __("dashboard.System Settings"))
                    ->collapsed(),
            ])
            ->discoverWidgets(
                in: app_path("Filament/Widgets"),
                for: "App\\Filament\\Widgets"
            )
            ->widgets([
                Widgets\AccountWidget::class,
                BookingChartsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
