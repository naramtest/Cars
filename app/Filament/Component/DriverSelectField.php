<?php

namespace App\Filament\Component;

use App\Models\Driver;
use Filament\Forms\Components\Select;

class DriverSelectField
{
    public static function make()
    {
        return Select::make("driver_id")
            ->label(__("dashboard.Driver"))
            ->relationship(
                name: "driver",
                modifyQueryUsing: fn($query) => $query
                    ->with("user")
                    ->orderBy("created_at", "desc")
            )
            ->searchable()
            ->getSearchResultsUsing(function (string $search) {
                return Driver::query()
                    ->join("users", "drivers.user_id", "=", "users.id")
                    ->where("users.name", "LIKE", "%{$search}%")
                    ->limit(50)
                    ->get()
                    ->map(function ($driver) {
                        return [
                            "id" => $driver->id,
                            "label" => $driver->full_name,
                        ];
                    });
            })
            ->getOptionLabelFromRecordUsing(
                fn(Driver $record) => $record->full_name
            )
            ->preload();
    }
}
