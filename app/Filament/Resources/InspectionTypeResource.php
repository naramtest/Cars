<?php

namespace App\Filament\Resources;

use App\Enums\TypesEnum;
use App\Filament\Forms\Type\TypeFormSchema;
use App\Filament\Resources\InspectionTypeResource\Pages;
use App\Filament\Resources\InspectionTypeResource\RelationManagers;
use App\Filament\Tables\Type\TypeTableSchema;
use App\Models\Type;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class InspectionTypeResource extends Resource
{
    use Translatable;

    protected static ?string $model = Type::class;

    protected static ?string $navigationIcon = "heroicon-o-clipboard-check";

    public static function form(Form $form): Form
    {
        return TypeFormSchema::make($form, TypesEnum::INSPECTION);
    }

    public static function table(Table $table): Table
    {
        return TypeTableSchema::schema($table, TypesEnum::INSPECTION);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ManageInspectionTypes::route("/"),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __("dashboard.checklist_items");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.checklist_item");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.checklist_items");
    }

    public static function getLabel(): ?string
    {
        return __("dashboard.checklist_item");
    }

    public static function getPluralLabel(): ?string
    {
        return __("dashboard.checklist_items");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.Business Management");
    }

    public static function getNavigationParentItem(): ?string
    {
        return __("dashboard.Inspections");
    }
}
