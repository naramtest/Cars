<?php

namespace App\Filament\Tables\Driver;

use App\Filament\Exports\DriverExporter;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class DriverTableSchema
{
    /**
     * @throws \Exception
     */
    public static function schema(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("user.name")
                    ->label(__("dashboard.name"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("user.email")
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
                DateRangeFilter::make("created_at")->label(
                    __("dashboard.Created At")
                ),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->defaultSort("created_at", "desc")
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
                    DriverExporter::class
                ),
            ])
            ->defaultSort("created_at", "desc");
    }
}
