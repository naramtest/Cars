<?php

namespace App\Filament\Tables\Customer;

use App\Filament\Exports\CustomerExporter;
use Exception;
use Filament\Tables;
use Filament\Tables\Table;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class CustomerTableSchema
{
    /**
     * @throws Exception
     */
    public static function schema(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->label(__("dashboard.name"))
                    ->searchable()
                    ->sortable(),

                PhoneColumn::make("phone_number")
                    ->label(__("dashboard.phone_number"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("email")
                    ->label(__("dashboard.email"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("bookings_count")
                    ->label(__("dashboard.Bookings"))
                    ->counts("bookings")
                    ->sortable()
                    ->visible(fn() => notDriver()),

                Tables\Columns\TextColumn::make("rents_count")
                    ->label(__("dashboard.Rents"))
                    ->counts("rents")
                    ->visible(fn() => notDriver())
                    ->sortable(),

                Tables\Columns\TextColumn::make("shippings_count")
                    ->label(__("dashboard.Shippings"))
                    ->counts("shippings")
                    ->visible(fn() => notDriver())
                    ->sortable(),

                Tables\Columns\TextColumn::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->visible(fn() => notDriver())
                    ->dateTime("M j, Y")
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->dateTime("M j, Y")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([Tables\Filters\TrashedFilter::make()])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
                    CustomerExporter::class
                ),
            ]);
    }
}
