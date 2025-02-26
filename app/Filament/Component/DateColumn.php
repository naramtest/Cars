<?php

namespace App\Filament\Component;

use Filament\Tables\Columns\TextColumn;

class DateColumn
{
    public static function make(
        string $column,
        string $label,
        bool $isToggled = true
    ) {
        return TextColumn::make($column)
            ->label($label)
            ->dateTime("M j, Y")
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: $isToggled);
    }
}
