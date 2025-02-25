<?php

namespace App\Filament\Forms\Addon;

use App\Enums\Addon\BillingType;
use App\Models\Addon;
use Filament\Forms;

class AddonFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make("name")
                                ->label(__("dashboard.name"))
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make("price")
                                ->label(__("dashboard.price"))
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->step(0.01),

                            Forms\Components\Select::make("currency")
                                ->label(__("dashboard.currency"))
                                ->options([
                                    "USD" => "USD",
                                    "EUR" => "EUR",
                                    "GBP" => "GBP",
                                    // Add more currencies as needed
                                ])
                                ->default("USD")
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
                        ])
                        ->columns([
                            "sm" => 2,
                        ])
                        ->columnSpan([
                            "sm" => 2,
                        ]),
                ])
                ->columnSpan([
                    "sm" => 2,
                ]),

            Forms\Components\Section::make(__("dashboard.Status"))
                ->schema([
                    Forms\Components\Placeholder::make("created_at")
                        ->label(__("dashboard.created_at"))
                        ->content(
                            fn(?Addon $record): string => $record
                                ? $record->created_at->diffForHumans()
                                : "-"
                        ),

                    Forms\Components\Placeholder::make("updated_at")
                        ->label(__("dashboard.updated_at"))
                        ->content(
                            fn(?Addon $record): string => $record
                                ? $record->updated_at->diffForHumans()
                                : "-"
                        ),
                ])
                ->hidden(fn(string $operation) => $operation !== "edit")
                ->columnSpan(1),
        ];
    }
}
