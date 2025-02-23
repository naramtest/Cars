<?php

namespace App\Filament\Tables\Driver;

use Filament\Tables;
use Filament\Tables\Table;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class DriverTableSchema
{
    public static function schema(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("first_name")
                    ->label(__("dashboard.first_name"))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make("last_name")
                    ->label(__("dashboard.last_name"))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make("email")
                    ->label(__("dashboard.email"))
                    ->searchable(),
                PhoneColumn::make("phone_number")
                    ->displayFormat(PhoneInputNumberType::INTERNATIONAL)
                    ->label(__("dashboard.phone_number"))
                    ->searchable(),
                Tables\Columns\TextColumn::make("license_number")
                    ->label(__("dashboard.license_number"))
                    ->searchable(),
                Tables\Columns\TextColumn::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->dateTime("M j, Y")
                    ->sortable(),
            ])
            ->filters([
                // Add your filters here
            ])
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
            ]);
    }
}
