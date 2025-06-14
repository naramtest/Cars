<?php

namespace App\Filament\Tables\Booking;

use App\Enums\ReservationStatus;
use App\Filament\Actions\Reservation\ReservationActions;
use App\Filament\Component\Customer\CustomerTableComponent;
use App\Filament\Component\DateColumn;
use App\Filament\Exports\BookingExporter;
use Exception;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class BookingTableSchema
{
    /**
     * @throws Exception
     */
    public static function schema(Table $table): Table
    {
        return $table
            ->dimCompleted([
                ReservationStatus::Completed,
                ReservationStatus::Cancelled,
            ])
            ->columns([
                Tables\Columns\TextColumn::make("reference_number")
                    ->label(__("dashboard.reference_number"))
                    ->searchable()
                    ->sortable(),

                ...CustomerTableComponent::make(),

                Tables\Columns\TextColumn::make("vehicle.name")
                    ->label(__("dashboard.Vehicle"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("driver.full_name")
                    ->label(__("dashboard.Driver"))
                    ->searchable(["drivers.first_name", "drivers.last_name"])
                    ->sortable()
                    ->visible(fn() => notDriver())
                    ->toggleable(),

                DateColumn::make(
                    "start_datetime",
                    __("dashboard.start_datetime"),
                    false
                ),
                DateColumn::make(
                    "end_datetime",
                    __("dashboard.end_datetime"),
                    false
                ),
                Tables\Columns\TextColumn::make("pickup_address")
                    ->label(__("dashboard.pickup_address"))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30),

                MoneyColumn::make("total_price")
                    ->label(__("dashboard.total_price"))
                    ->sortable()
                    ->visible(fn() => notDriver())
                    ->toggleable(),

                Tables\Columns\TextColumn::make("status")
                    ->label(__("dashboard.status"))
                    ->badge()
                    ->sortable(),

                DateColumn::make("created_at", __("dashboard.created_at")),
                DateColumn::make("updated_at", __("dashboard.updated_at")),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make("status")
                    ->label(__("dashboard.status"))
                    ->options(fn(): string => ReservationStatus::class)
                    ->multiple(),
                DateRangeFilter::make("start_datetime")->label(
                    __("dashboard.start_datetime")
                ),
                DateRangeFilter::make("create_at")->label(
                    __("dashboard.Created At")
                ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),

                ReservationActions::make()->visible(fn() => notDriver()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->label(__("dashboard.export_selected"))
                    ->exporter(BookingExporter::class),
            ])
            ->defaultSort("created_at", "desc");
    }
}
