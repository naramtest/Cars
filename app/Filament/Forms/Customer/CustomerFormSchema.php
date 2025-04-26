<?php

namespace App\Filament\Forms\Customer;

use Filament\Forms;
use Filament\Forms\Form;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class CustomerFormSchema
{
    public static function schema(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make("name")
                        ->label(__("dashboard.name"))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make("email")
                        ->label(__("dashboard.email"))
                        ->email()
                        ->maxLength(255),

                    PhoneInput::make("phone_number")
                        ->label(__("dashboard.phone_number"))
                        ->required(),

                    Forms\Components\Textarea::make("notes")
                        ->label(__("dashboard.notes"))
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ])
                ->columns(),
        ]);
    }
}
