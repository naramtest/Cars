<?php

namespace App\Filament\Actions\Type;

use App\Enums\TypesEnum;
use App\Models\Type;
use Auth;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;

class TypeActions
{
    public static function actions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()->hidden(function (Type $record) {
                return !Auth::user()->can("delete", $record) or
                    !TypesEnum::getExists($record);
            }),

            //            self::moveAndDeleteAction(),
        ];
    }
}
