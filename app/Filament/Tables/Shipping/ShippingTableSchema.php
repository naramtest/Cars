<?php

namespace App\Filament\Tables\Shipping;

use App\Enums\Shipping\ShippingStatus;
use App\Filament\Component\DateColumn;
use App\Filament\Exports\ShippingExporter;
use App\Models\Shipping;
use Exception;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class ShippingTableSchema
{
    /**
     * @throws Exception
     */
    public static function schema(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("reference_number")
                    ->label(__("dashboard.tracking_number"))
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

                Tables\Columns\TextColumn::make("driver.full_name")
                    ->label(__("dashboard.Driver"))
                    ->searchable(["drivers.first_name", "drivers.last_name"])
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("total_weight")
                    ->label(__("dashboard.total_weight"))
                    ->formatStateUsing(fn(float $state): string => "$state kg")
                    ->sortable(),

                Tables\Columns\TextColumn::make("received_at")
                    ->label(__("dashboard.received_at"))
                    ->dateTime("M j, Y H:i")
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("delivered_at")
                    ->label(__("dashboard.delivered_at"))
                    ->dateTime("M j, Y H:i")
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("items_count")
                    ->label(__("dashboard.items"))
                    ->formatStateUsing(
                        fn(int $state): string => "$state" .
                            " " .
                            __("dashboard.items")
                    )
                    ->getStateUsing(
                        fn(Shipping $record): int => $record->items()->count()
                    )
                    ->sortable(
                        query: function (
                            Builder $query,
                            string $direction
                        ): Builder {
                            return $query
                                ->withCount("items")
                                ->orderBy("items_count", $direction);
                        }
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make("status")
                    ->label(__("dashboard.status"))
                    ->badge()
                    ->sortable(),

                DateColumn::make("created_at", __("dashboard.created_at")),
                DateColumn::make("updated_at", __("dashboard.updated_at")),
            ])
            ->filters([
                SelectFilter::make("status")
                    ->label(__("dashboard.status"))
                    ->options(ShippingStatus::class)
                    ->multiple(),
                SelectFilter::make("driver_id")
                    ->label(__("dashboard.Driver"))
                    ->relationship("driver", "first_name")
                    ->getOptionLabelFromRecordUsing(
                        fn($record) => "$record->first_name $record->last_name"
                    )
                    ->searchable()
                    ->preload(),
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
                Tables\Actions\ActionGroup::make([
                    Action::make("pickUp")
                        ->label(__("dashboard.mark_as_picked_up"))
                        ->icon("heroicon-o-truck")
                        ->color("info")
                        ->action(function (Shipping $record) {
                            $record->update([
                                "status" => ShippingStatus::Picked_Up->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Shipping $record) => $record->status ==
                                ShippingStatus::Pending
                        ),

                    Action::make("deliver")
                        ->label(__("dashboard.mark_as_delivered"))
                        ->icon("heroicon-o-check-circle")
                        ->color("success")
                        ->action(function (Shipping $record) {
                            $record->update([
                                "status" => ShippingStatus::Delivered->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Shipping $record) => $record->status ==
                                ShippingStatus::Picked_Up
                        ),

                    Action::make("confirm")
                        ->label(__("dashboard.confirm_shipping"))
                        ->icon("heroicon-o-check")
                        ->color("primary")
                        ->action(function (Shipping $record) {
                            $record->update([
                                "status" => ShippingStatus::Confirmed,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Shipping $record) => $record->status ==
                                ShippingStatus::Pending
                        ),

                    Action::make("cancel")
                        ->label(__("dashboard.cancel_shipping"))
                        ->icon("heroicon-o-x-circle")
                        ->color("danger")
                        ->action(function (Shipping $record) {
                            $record->update([
                                "status" => ShippingStatus::Cancelled->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Shipping $record) => $record->status !=
                                ShippingStatus::Delivered &&
                                $record->status != ShippingStatus::Cancelled
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()->exporter(
                    ShippingExporter::class
                ),
            ]);
    }
}
