<?php

namespace App\Filament\Component;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class CustomNameSlugField
{
    public static function getCustomTitleField(
        ?string $label = null,
        string $fieldName = "title",
        string $slugColumn = "slug",
        int $length = 250
    ): TextInput {
        return TextInput::make($fieldName)
            ->label($label ?? __("dashboard.Title"))
            ->maxLength($length)
            ->live(onBlur: true)
            ->counter($fieldName, $length)
            ->autofocus()
            ->unique(ignoreRecord: true)
            ->required()
            ->afterStateUpdated(function (
                string $operation,
                $state,
                callable $set
            ) use ($slugColumn) {
                if ($operation == "create" or $operation == "createOption") {
                    return $set($slugColumn, Str::slug($state, language: null));
                }

                return null;
            });
    }

    public static function getCustomSlugField(bool $unique = true): TextInput
    {
        $textInput = TextInput::make("slug")
            ->required()
            ->live(onBlur: true)
            ->afterStateUpdated(
                callback: function (TextInput $component, $state) {
                    $component->state(Str::slug($state, language: null));
                }
            );
        if ($unique) {
            $textInput->unique(ignoreRecord: true);
        }
        return $textInput;
    }
}
