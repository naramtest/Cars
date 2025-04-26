<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Rent\RentFormSchema;
use App\Filament\Resources\RentResource\Pages;
use App\Filament\Tables\Rent\RentTableSchema;
use App\Models\Rent;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RentResource extends Resource
{
    protected static ?string $model = Rent::class;

    protected static ?string $navigationIcon = "gmdi-car-rental-o";

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema(RentFormSchema::schema());
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return RentTableSchema::schema($table);
    }

    public static function getRelations(): array
    {
        return [
                //
            ];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListRents::route("/"),
            "create" => Pages\CreateRent::route("/create"),
            "edit" => Pages\EditRent::route("/{record}/edit"),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    public static function getNavigationLabel(): string
    {
        return __("dashboard.Rents");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.Rent");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.Rents");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.Operations");
    }

    public static function getLabel(): ?string
    {
        return __("dashboard.Rent");
    }

    public static function getPluralLabel(): ?string
    {
        return __("dashboard.Rents");
    }
}
