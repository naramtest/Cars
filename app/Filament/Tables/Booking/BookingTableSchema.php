<?php

namespace App\Filament\Tables\Booking;

use App\Enums\Booking\BookingStatus;
use App\Filament\Component\DateColumn;
use App\Filament\Exports\BookingExporter;
use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class BookingTableSchema
{
    /**
     * @throws \Exception
     */
    public static function schema(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("id")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Columns\TextColumn::make("driver.full_name")
                    ->label(__("dashboard.Driver"))
                    ->searchable(["drivers.first_name", "drivers.last_name"])
                    ->sortable()
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
                                "DATEDIFF(end_datetime, start_datetime) " .
                                    $direction
                            );
                        }
                    ),

                MoneyColumn::make("total_price")
                    ->label(__("dashboard.total_price"))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("status")
                    ->label(__("dashboard.status"))
                    ->sortable(),

                DateColumn::make("created_at", __("dashboard.created_at")),
                DateColumn::make("updated_at", __("dashboard.updated_at")),
            ])
            ->filters([
                SelectFilter::make("status")
                    ->label(__("dashboard.status"))
                    ->options(fn(): string => BookingStatus::class)
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
                Tables\Actions\ActionGroup::make([
                    Action::make("complete")
                        ->label(__("dashboard.mark_as_completed"))
                        ->icon("heroicon-o-check-circle")
                        ->color("success")
                        ->action(function (Booking $record) {
                            $record->update([
                                "status" => BookingStatus::Completed->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Booking $record) => $record->status ==
                                BookingStatus::OnGoing
                        ),

                    Action::make("start")
                        ->label(__("dashboard.mark_as_ongoing"))
                        ->icon("heroicon-o-play")
                        ->color("warning")
                        ->action(function (Booking $record) {
                            $record->update([
                                "status" => BookingStatus::OnGoing->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Booking $record) => $record->status ==
                                BookingStatus::Pending
                        ),

                    Action::make("cancel")
                        ->label(__("dashboard.cancel_booking"))
                        ->icon("heroicon-o-x-circle")
                        ->color("danger")
                        ->action(function (Booking $record) {
                            $record->update([
                                "status" => BookingStatus::Cancelled->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Booking $record) => in_array($record->status, [
                                BookingStatus::Pending,
                                BookingStatus::OnGoing,
                            ])
                        ),
                ]),
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
            ]);
    }
}
