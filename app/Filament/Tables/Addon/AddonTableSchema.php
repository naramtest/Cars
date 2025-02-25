<?php

namespace App\Filament\Tables\Addon;

use App\Enums\Addon\BillingType;
use Filament\Tables;
use Filament\Tables\Table;

class AddonTableSchema
{
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

                Tables\Columns\TextColumn::make("formatted_price")
                    ->label(__("dashboard.price"))
                    ->sortable("price"),

                Tables\Columns\TextColumn::make("currency")
                    ->label(__("dashboard.currency"))
                    ->sortable(),

                Tables\Columns\TextColumn::make("billing_type")
                    ->label(__("dashboard.billing_type"))
                    ->badge()
                    ->color(
                        fn(BillingType $state): string => match ($state) {
                            BillingType::Daily => "warning",
                            BillingType::Total => "success",
                        }
                    ),

                Tables\Columns\IconColumn::make("is_active")
                    ->label(__("dashboard.is_active"))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("billing_type")
                    ->label(__("dashboard.billing_type"))
                    ->options(BillingType::class),

                Tables\Filters\TernaryFilter::make("is_active")
                    ->label(__("dashboard.is_active"))
                    ->nullable(),
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
