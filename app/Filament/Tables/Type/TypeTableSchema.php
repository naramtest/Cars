<?php

namespace App\Filament\Tables\Type;

use App\Filament\Actions\Type\TypeActions;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TypeTableSchema
{
    public static function schema(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("order")
                    ->label(__("dashboard.Order"))
                    ->grow(false)
                    ->sortable()
                    ->grow(false),
                TextColumn::make("name")
                    ->label(__("dashboard.name"))
                    ->sortable()
                    ->searchable(),
                IconColumn::make("is_visible")
                    ->label(__("dashboard.Visible"))
                    ->boolean(),
                TextColumn::make("created_at")
                    ->label(__("dashboard.Created At"))
                    ->date("M j, Y")
                    ->sortable(),
            ])
            ->defaultSort("order")
            ->reorderable("order")
            ->actions(TypeActions::actions());
    }
}
