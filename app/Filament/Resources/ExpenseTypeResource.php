<?php

namespace App\Filament\Resources;

use App\Enums\TypesEnum;
use App\Filament\Forms\Type\TypeFormSchema;
use App\Filament\Resources\ExpenseTypeResource\Pages;
use App\Filament\Resources\ExpenseTypeResource\RelationManagers;
use App\Filament\Tables\Type\TypeTableSchema;
use App\Models\Type;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ExpenseTypeResource extends Resource
{
    use Translatable;

    protected static ?string $model = Type::class;

    protected static ?string $navigationIcon = "heroicon-o-rectangle-stack";

    public static function form(Form $form): Form
    {
        return TypeFormSchema::make($form, TypesEnum::EXPENSE);
    }

    public static function table(Table $table): Table
    {
        return TypeTableSchema::schema($table, TypesEnum::EXPENSE);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ManageExpenseTypes::route("/"),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __("dashboard.types");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.type");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.types");
    }

    public static function getLabel(): ?string
    {
        return __("dashboard.type");
    }

    public static function getPluralLabel(): ?string
    {
        return __("dashboard.types");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.Business Management");
    }

    public static function getNavigationParentItem(): ?string
    {
        return __("dashboard.Expenses");
    }
}
