<?php

namespace App\Filament\Tables\Inspection;

use App\Enums\Inspection\InspectionStatus;
use App\Enums\Inspection\RepairStatus;
use App\Filament\Component\DateColumn;
use App\Filament\Exports\InspectionExporter;
use App\Models\Inspection;
use Exception;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class InspectionTableSchema
{
    /**
     * @throws Exception
     */
    public static function schema(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("id")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make("vehicle.name")
                    ->label(__("dashboard.Vehicle"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("inspection_by")
                    ->label(__("dashboard.inspection_by"))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("inspection_date")
                    ->label(__("dashboard.inspection_date"))
                    ->date("M j, Y")
                    ->sortable(),

                Tables\Columns\TextColumn::make("status")
                    ->label(__("dashboard.inspection_status"))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make("repair_status")
                    ->label(__("dashboard.repair_status"))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make("meter_reading_km")
                    ->label(__("dashboard.meter_reading_km"))
                    ->formatStateUsing(
                        fn($state) => $state
                            ? number_format($state) . " km"
                            : "-"
                    )
                    ->sortable()
                    ->toggleable(),

                MoneyColumn::make("amount")
                    ->label(__("dashboard.amount"))
                    ->sortable()
                    ->toggleable(),

                DateColumn::make("created_at", __("dashboard.created_at")),
                DateColumn::make("updated_at", __("dashboard.updated_at")),
            ])
            ->filters([
                SelectFilter::make("status")
                    ->label(__("dashboard.inspection_status"))
                    ->options(InspectionStatus::class)
                    ->multiple(),

                SelectFilter::make("repair_status")
                    ->label(__("dashboard.repair_status"))
                    ->options(RepairStatus::class)
                    ->multiple(),

                DateRangeFilter::make("inspection_date")->label(
                    __("dashboard.inspection_date")
                ),

                DateRangeFilter::make("created_at")->label(
                    __("dashboard.Created At")
                ),

                SelectFilter::make("vehicle_id")
                    ->label(__("dashboard.Vehicle"))
                    ->relationship("vehicle", "name")
                    ->searchable()
                    ->preload(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ActionGroup::make([
                    Action::make("complete")
                        ->label(__("dashboard.mark_as_completed"))
                        ->icon("heroicon-o-check-circle")
                        ->color("success")
                        ->action(function (Inspection $record) {
                            $record->update([
                                "status" => InspectionStatus::Completed->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Inspection $record) => $record->status !=
                                InspectionStatus::Completed
                        ),

                    Action::make("needsRepair")
                        ->label(__("dashboard.mark_as_needs_repair"))
                        ->icon("heroicon-o-wrench")
                        ->color("danger")
                        ->action(function (Inspection $record) {
                            $record->update([
                                "repair_status" =>
                                    RepairStatus::NeedsRepair->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Inspection $record) => $record->repair_status !=
                                RepairStatus::NeedsRepair
                        ),

                    Action::make("inProgressRepair")
                        ->label(__("dashboard.mark_as_in_progress_repair"))
                        ->icon("heroicon-o-play")
                        ->color("warning")
                        ->action(function (Inspection $record) {
                            $record->update([
                                "repair_status" =>
                                    RepairStatus::InProgress->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Inspection $record) => $record->repair_status !=
                                RepairStatus::InProgress
                        ),

                    Action::make("repairCompleted")
                        ->label(__("dashboard.mark_as_repair_completed"))
                        ->icon("heroicon-o-check")
                        ->color("success")
                        ->action(function (Inspection $record) {
                            $record->update([
                                "repair_status" =>
                                    RepairStatus::Completed->value,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Inspection $record) => $record->repair_status !=
                                RepairStatus::Completed
                        ),
                ]),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()->exporter(
                    InspectionExporter::class
                ),
            ])
            ->defaultSort("created_at", "desc");
    }
}
