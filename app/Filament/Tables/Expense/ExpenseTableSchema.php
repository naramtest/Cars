<?php

namespace App\Filament\Tables\Expense;

use App\Filament\Component\DateColumn;
use App\Filament\Exports\ExpenseExporter;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class ExpenseTableSchema
{
    /**
     * @throws \Exception
     */
    public static function schema(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("title")
                    ->label(__("dashboard.title"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("vehicle.name")
                    ->label(__("dashboard.Vehicle"))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("expense_date")
                    ->label(__("dashboard.expense_date"))
                    ->date("M j, Y")
                    ->sortable(),

                MoneyColumn::make("amount")
                    ->label(__("dashboard.amount"))
                    ->sortable(),

                Tables\Columns\TextColumn::make("types.name")
                    ->label(__("dashboard.types"))
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable()
                    ->toggleable(),

                DateColumn::make("created_at", __("dashboard.created_at")),
                DateColumn::make("updated_at", __("dashboard.updated_at")),
            ])
            ->filters([
                DateRangeFilter::make("expense_date")->label(
                    __("dashboard.expense_date")
                ),
                DateRangeFilter::make("created_at")->label(
                    __("dashboard.Created At")
                ),
                Tables\Filters\SelectFilter::make("vehicle_id")
                    ->label(__("dashboard.Vehicle"))
                    ->relationship("vehicle", "name")
                    ->searchable()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->defaultSort("expense_date", "desc")
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
                    ExpenseExporter::class
                ),
            ])
            ->defaultSort("created_at", "desc");
    }
}
