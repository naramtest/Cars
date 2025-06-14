<?php

namespace App\Filament\Pages;

use App\Enums\SocialNetwork;
use App\Settings\InfoSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Resources\Concerns\Translatable;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class Info extends SettingsPage
{
    use Translatable;
    use HasPageShield;

    protected static ?string $navigationIcon = "heroicon-o-cog-6-tooth";
    protected static string $settings = InfoSettings::class;

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.System Settings");
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make(__("dashboard.info.title"))
                ->schema([
                    Tab::make("Info")
                        ->label(__("dashboard.info"))
                        ->icon("gmdi-info-o")
                        ->schema([
                            TextInput::make("name")
                                ->required()
                                ->label(__("dashboard.name"))
                                ->maxLength(50)
                                ->translatable()
                                ->columnSpan(1),
                            TextInput::make("slogan")
                                ->label(__("dashboard.slogan"))
                                ->translatable()
                                ->columnSpan(1),
                            Textarea::make("about")
                                ->label(__("dashboard.about"))
                                ->maxLength(400)
                                ->rows(3)
                                ->translatable()
                                ->columnSpan(1),
                            Textarea::make("address")
                                ->label(__("dashboard.address"))
                                ->columnSpan(1)
                                ->rows(3)
                                ->maxLength(250)
                                ->translatable(),
                            PhoneInput::make("support_whatsapp_number")
                                ->initialCountry("AE")
                                ->label(__("dashboard.support_whatsapp_number"))
                                ->prefixIcon("gmdi-whatsapp-o")
                                ->columnSpan(1),
                        ])
                        ->columns(),
                    Tab::make("Contact")
                        ->label(__("dashboard.contacts"))
                        ->icon("gmdi-contacts-o")
                        ->schema([
                            Repeater::make("phones")
                                ->schema([
                                    PhoneInput::make("value")
                                        ->initialCountry("AE")
                                        ->label("Phone Number")
                                        ->required()
                                        ->hiddenLabel(),
                                ])
                                ->columnSpan(1)
                                ->label(__("dashboard.phone")),

                            Repeater::make("emails")
                                ->schema([
                                    TextInput::make("value")
                                        ->email()
                                        ->hiddenLabel()
                                        ->maxLength(100)
                                        ->prefixIcon("gmdi-email-o"),
                                ])
                                ->columnSpan(1)
                                ->label(__("dashboard.email")),
                        ])
                        ->columns(),
                    Tab::make("Admins Phone Numbers")
                        ->icon("gmdi-phone-o")
                        ->label(__("dashboard.admin_phone_numbers"))
                        ->schema([
                            Repeater::make("admin_phones")
                                ->columns()
                                ->hiddenLabel()
                                ->schema([
                                    PhoneInput::make("number")
                                        ->initialCountry("AE")
                                        ->label(__("dashboard.phone"))
                                        ->required()
                                        ->columnSpan(1)
                                        ->hiddenLabel(),
                                    TextInput::make("name")
                                        ->label(__("dashboard.name"))
                                        ->required()
                                        ->columnSpan(1)
                                        ->hiddenLabel()
                                        ->placeholder(
                                            __("dashboard.Admin Name")
                                        )
                                        ->maxLength(100),
                                ])
                                ->columnSpan(1)
                                ->label(__("dashboard.admin_phone_numbers"))
                                ->helperText(
                                    __(
                                        "dashboard.Phone numbers that will receive WhatsApp notifications"
                                    )
                                )
                                ->defaultItems(1),
                        ]),

                    Tab::make("Social Media")
                        ->label(__("dashboard.social_title"))
                        ->icon("gmdi-tag-o")
                        ->schema([
                            Repeater::make("socials")
                                ->schema([
                                    Select::make("name")
                                        ->options(SocialNetwork::class)
                                        ->live(onBlur: true)
                                        ->hiddenLabel()
                                        ->selectablePlaceholder(false)
                                        ->prefixIcon("gmdi-share-o")
                                        ->columnSpan(1),
                                    TextInput::make("url")
                                        ->url()
                                        ->hiddenLabel()
                                        ->maxLength(100)
                                        ->prefixIcon("gmdi-link-o")
                                        ->columnSpan(2),
                                ])
                                ->columns(3)
                                ->defaultItems(1)
                                ->hiddenLabel()
                                ->label(trans("dashboard.social_title")),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}
