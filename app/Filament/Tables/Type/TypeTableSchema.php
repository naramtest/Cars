<?php

namespace App\Filament\Tables\Type;

use App\Enums\CategoryType;
use App\Enums\TypesEnum;
use App\Filament\Actions\Type\TypeActions;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TypeTableSchema
{
    public static function schema(Table $table, TypesEnum $type): Table
    {
        return $table
            ->defaultSort("created_at", "desc")
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
            ->actions(TypeActions::actions())
            ->modifyQueryUsing(function (Builder $query) use ($type) {
                return $query->where("type", $type);
            });
    }
}
