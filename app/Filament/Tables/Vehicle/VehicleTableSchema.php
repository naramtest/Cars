<?php

namespace App\Filament\Tables\Vehicle;

use App\Enums\Vehicle\FuelType;
use App\Enums\Vehicle\GearboxType;
use App\Filament\Exports\VehicleExporter;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class VehicleTableSchema
{
    /**
     * @throws \Exception
     */
    public static function schema(Table $table): Table
    {
        return $table
            ->defaultSort("created_at", "desc")
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->label(__("dashboard.name"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("model")
                    ->label(__("dashboard.model"))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make("driver.full_name")
                    ->label(__("dashboard.Driver"))
                    ->searchable(["driver.user.name"])
                    ->sortable(["driver.user.name"]),

                Tables\Columns\TextColumn::make("license_plate")
                    ->label(__("dashboard.license_plate"))
                    ->searchable(),

                MoneyColumn::make("daily_rate")
                    ->label(__("dashboard.daily_rate"))
                    ->sortable(),

                Tables\Columns\TextColumn::make("gearbox")
                    ->label(__("dashboard.gearbox"))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make("fuel_type")
                    ->label(__("dashboard.fuel_type"))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make("registration_expiry_date")
                    ->label(__("dashboard.registration_expiry_date"))
                    ->date("M j, Y")
                    ->sortable(),
            ])
            ->filters([
                DateRangeFilter::make("registration_expiry_date")->label(
                    __("dashboard.registration_expiry_date")
                ),
                DateRangeFilter::make("created_at")->label(
                    __("dashboard.Created At")
                ),

                Tables\Filters\SelectFilter::make("gearbox")
                    ->label(__("dashboard.gearbox"))
                    ->options(GearboxType::class),

                Tables\Filters\SelectFilter::make("fuel_type")
                    ->label(__("dashboard.fuel_type"))
                    ->options(FuelType::class),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->defaultSort("created_at", "desc")
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()->exporter(
                    VehicleExporter::class
                ),
            ]);
    }
}
