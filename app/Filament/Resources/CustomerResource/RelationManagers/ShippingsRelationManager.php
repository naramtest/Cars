<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Enums\Shipping\ShippingStatus;
use App\Filament\Component\DateColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class ShippingsRelationManager extends RelationManager
{
    protected static string $relationship = "shippings";

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute("reference_number")
            ->columns([
                Tables\Columns\TextColumn::make("reference_number")
                    ->label(__("dashboard.tracking_number"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("driver.full_name")
                    ->label(__("dashboard.Driver"))
                    ->searchable(["drivers.first_name", "drivers.last_name"])
                    ->sortable()
                    ->toggleable(),

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

                Tables\Columns\TextColumn::make("status")
                    ->label(__("dashboard.status"))
                    ->badge()
                    ->sortable(),

                DateColumn::make("created_at", __("dashboard.created_at")),
            ])
            ->filters([
                DateRangeFilter::make("create_at")->label(
                    __("dashboard.Created at")
                ),
                Tables\Filters\SelectFilter::make("status")
                    ->label(__("dashboard.status"))
                    ->options(ShippingStatus::class)
                    ->multiple(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                //TODO : add action to convert status (Rent , shipping already exist in the resource but I have to add one for booking here and in Booking resource)
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(
                fn(Builder $query) => $query->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ])
            );
    }
}
