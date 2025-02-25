<?php

namespace App\Filament\Forms\Addon;

use App\Enums\Addon\BillingType;
use Filament\Forms;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class AddonFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make("name")
                    ->label(__("dashboard.name"))
                    ->required()
                    ->maxLength(255),

                MoneyInput::make("price")
                    ->label(__("dashboard.price"))
                    ->required(),
                Forms\Components\Select::make("billing_type")
                    ->label(__("dashboard.billing_type"))
                    ->options(BillingType::class)
                    ->required(),

                Forms\Components\Textarea::make("description")
                    ->label(__("dashboard.description"))
                    ->rows(3)
                    ->maxLength(65535),

                Forms\Components\Toggle::make("is_active")
                    ->label(__("dashboard.is_active"))
                    ->default(true),
            ]),
        ];
    }
}
