<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Enums\ReservationStatus;
use App\Filament\Component\DateColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class RentsRelationManager extends RelationManager
{
    protected static string $relationship = "rents";

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute("reference_number")
            ->columns([
                Tables\Columns\TextColumn::make("reference_number")
                    ->label(__("dashboard.reference_number"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("vehicle.name")
                    ->label(__("dashboard.Vehicle"))
                    ->searchable()
                    ->sortable(),

                DateColumn::make(
                    "rental_start_date",
                    __("dashboard.start_datetime"),
                    false
                ),
                DateColumn::make(
                    "rental_end_date",
                    __("dashboard.end_datetime"),
                    false
                ),

                Tables\Columns\TextColumn::make("duration_in_days")
                    ->label(__("dashboard.duration"))
                    ->formatStateUsing(
                        fn(int $state): string => $state .
                            " " .
                            __("dashboard.days")
                    )
                    ->toggleable(),

                MoneyColumn::make("total_price")
                    ->label(__("dashboard.total_price"))
                    ->sortable(),

                Tables\Columns\TextColumn::make("status")
                    ->label(__("dashboard.status"))
                    ->badge()
                    ->sortable(),

                DateColumn::make("created_at", __("dashboard.created_at")),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("status")
                    ->label(__("dashboard.status"))
                    ->options(ReservationStatus::class)
                    ->multiple(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
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
