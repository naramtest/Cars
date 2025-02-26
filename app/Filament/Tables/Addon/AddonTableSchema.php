<?php

namespace App\Filament\Tables\Addon;

use App\Enums\Addon\BillingType;
use App\Filament\Component\DateColumn;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class AddonTableSchema
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

                Tables\Columns\TextColumn::make("name")
                    ->label(__("dashboard.name"))
                    ->searchable()
                    ->sortable(),

                MoneyColumn::make("price")
                    ->label(__("dashboard.price"))
                    ->sortable("price"),

                Tables\Columns\TextColumn::make("billing_type")
                    ->label(__("dashboard.billing_type"))
                    ->badge(),
                Tables\Columns\IconColumn::make("is_active")
                    ->label(__("dashboard.is_active"))
                    ->boolean()
                    ->sortable(),

                DateColumn::make("created_at", __("dashboard.created_at")),
                DateColumn::make("updated_at", __("dashboard.updated_at")),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("billing_type")
                    ->label(__("dashboard.billing_type"))
                    ->options(BillingType::class),

                Tables\Filters\TernaryFilter::make("is_active")
                    ->label(__("dashboard.is_active"))
                    ->nullable(),
                DateRangeFilter::make("created_at")->label(
                    __("dashboard.Created At")
                ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
