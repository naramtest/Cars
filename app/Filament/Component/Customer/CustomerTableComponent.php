<?php

namespace App\Filament\Component\Customer;

use App\Filament\Resources\CustomerResource;
use Filament\Tables\Columns\TextColumn;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class CustomerTableComponent
{
    public static function make()
    {
        return [
            TextColumn::make("customers.name")
                ->label(__("dashboard.client_name"))
                ->searchable()
                ->url(
                    fn($record) => $record->customers->first()
                        ? CustomerResource::getUrl("view", [
                            "record" => $record->customers->first()->id,
                        ])
                        : null
                )
                ->openUrlInNewTab()
                ->color("primary")
                ->sortable(),

            PhoneColumn::make("customers.phone_number")
                ->label(__("dashboard.client_phone"))
                ->searchable()
                ->url(
                    fn($record) => $record->customers->first()
                        ? "tel:" . $record->customers->first()->phone_number
                        : null
                )
                ->color("info")
                ->sortable(),
        ];
    }
}
