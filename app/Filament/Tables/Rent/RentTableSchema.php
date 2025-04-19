<?php

namespace App\Filament\Tables\Rent;

use App\Enums\ReservationStatus;
use App\Filament\Actions\Reservation\ReservationActions;
use App\Filament\Component\DateColumn;
use App\Filament\Exports\RentExporter;
use Exception;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class RentTableSchema
{
    /**
     * @throws Exception
     */
    public static function schema(Table $table): Table
    {
        return $table
            ->defaultSort("created_at", "desc")
            ->columns([
                Tables\Columns\TextColumn::make("reference_number")
                    ->label(__("dashboard.reference_number"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("client_name")
                    ->label(__("dashboard.client_name"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("client_email")
                    ->label(__("dashboard.client_email"))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                PhoneColumn::make("client_phone")
                    ->label(__("dashboard.client_phone"))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("vehicle.name")
                    ->label(__("dashboard.Vehicle"))
                    ->searchable()
                    ->sortable(),

                DateColumn::make(
                    "rental_start_date",
                    __("dashboard.start_datetime"),
                    false
                ),
                DateColumn::make(
                    "rental_end_date",
                    __("dashboard.end_datetime"),
                    false
                ),

                Tables\Columns\TextColumn::make("duration_in_days")
                    ->label(__("dashboard.duration"))
                    ->formatStateUsing(
                        fn(int $state): string => $state .
                            " " .
                            __("dashboard.days")
                    )
                    ->sortable(
                        query: function (
                            Builder $query,
                            string $direction
                        ): Builder {
                            return $query->orderByRaw(
                                "DATEDIFF(rental_end_date, rental_start_date) " .
                                    $direction
                            );
                        }
                    )
                    ->toggleable(),

                MoneyColumn::make("total_price")
                    ->label(__("dashboard.total_price"))
                    ->sortable()
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
                    ->options(ReservationStatus::class)
                    ->multiple(),
                DateRangeFilter::make("rental_start_date")->label(
                    __("dashboard.start_datetime")
                ),
                DateRangeFilter::make("rental_end_date")->label(
                    __("dashboard.end_datetime")
                ),
                DateRangeFilter::make("created_at")->label(
                    __("dashboard.Created At")
                ),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                ReservationActions::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()->exporter(
                    RentExporter::class
                ),
            ]);
    }
}
