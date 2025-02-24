<?php

namespace App\Filament\Forms\Type;

use App\Enums\TypesEnum;
use App\Filament\Component\CustomNameSlugField;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class TypeFormSchema
{
    public static function make(Form $form, TypesEnum $type): Form
    {
        return $form->schema([
            Toggle::make("is_visible")
                ->label(__("dashboard.Visible"))
                ->default(true)
                ->columnSpan(2),
            CustomNameSlugField::getCustomTitleField(
                label: __("dashboard.name"),
                fieldName: "name"
            ),
            CustomNameSlugField::getCustomSlugField(),
            Textarea::make("description")
                ->label(__("dashboard.Description"))
                ->rows(3)
                ->maxLength(160)
                ->columnSpan(2),
            Hidden::make("type")->default($type),
        ]);
    }
}
