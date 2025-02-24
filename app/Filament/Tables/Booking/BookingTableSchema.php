<?php

namespace App\Filament\Tables\Booking;

use App\Enums\Booking\BookingStatus;
use App\Models\Booking;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingTableSchema
{
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
                    ->toggleable(),

                Tables\Columns\TextColumn::make("client_phone")
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

                Tables\Columns\TextColumn::make("start_datetime")
                    ->label(__("dashboard.start_datetime"))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make("end_datetime")
                    ->label(__("dashboard.end_datetime"))
                    ->dateTime()
                    ->sortable(),

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

                Tables\Columns\TextColumn::make("total_price")
                    ->label(__("dashboard.total_price"))
                    ->formatStateUsing(
                        fn(float $state): string => number_format($state, 2) .
                            " " .
                            __("dashboard.currency")
                    )
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make("status")
                    ->label(__("dashboard.status"))
                    ->icon(
                        fn(BookingStatus $state): string => match ($state) {
                            BookingStatus::Completed
                                => "heroicon-o-check-circle",
                            BookingStatus::OnGoing => "heroicon-o-arrow-path",
                            BookingStatus::Pending => "heroicon-o-clock",
                            BookingStatus::Cancelled => "heroicon-o-x-circle",
                        }
                    )
                    ->color(
                        fn(BookingStatus $state): string => $state->getColor()
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make("status")
                    ->label(__("dashboard.status"))
                    ->options(fn(): string => BookingStatus::class)
                    ->multiple(),

                Filter::make("created_at")
                    ->form([
                        DatePicker::make("created_from")->label(
                            __("dashboard.created_from")
                        ),
                        DatePicker::make("created_until")->label(
                            __("dashboard.created_until")
                        ),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data["created_from"],
                                fn(
                                    Builder $query,
                                    $date
                                ): Builder => $query->whereDate(
                                    "created_at",
                                    ">=",
                                    $date
                                )
                            )
                            ->when(
                                $data["created_until"],
                                fn(
                                    Builder $query,
                                    $date
                                ): Builder => $query->whereDate(
                                    "created_at",
                                    "<=",
                                    $date
                                )
                            );
                    }),

                Filter::make("booking_date")
                    ->form([
                        DatePicker::make("date_from")->label(
                            __("dashboard.date_from")
                        ),
                        DatePicker::make("date_until")->label(
                            __("dashboard.date_until")
                        ),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data["date_from"],
                                fn(
                                    Builder $query,
                                    $date
                                ): Builder => $query->whereDate(
                                    "start_datetime",
                                    ">=",
                                    $date
                                )
                            )
                            ->when(
                                $data["date_until"],
                                fn(
                                    Builder $query,
                                    $date
                                ): Builder => $query->whereDate(
                                    "end_datetime",
                                    "<=",
                                    $date
                                )
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    //                    Tables\Actions\ExportBulkAction::make()
                    //                        ->label(__("dashboard.export_selected"))
                    //                        ->exporter(BookingExporter::class),
                ]),
            ]);
    }
}
