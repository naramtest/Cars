<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Vehicle\VehicleFormSchema;
use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Filament\Tables\Vehicle\VehicleTableSchema;
use App\Models\Vehicle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = "gmdi-directions-car-o";

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema(VehicleFormSchema::schema());
    }

    public static function table(Table $table): Table
    {
        return VehicleTableSchema::schema($table);
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
            "index" => Pages\ListVehicles::route("/"),
            "create" => Pages\CreateVehicle::route("/create"),
            "edit" => Pages\EditVehicle::route("/{record}/edit"),
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
        return __("dashboard.Vehicles");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.Vehicle");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.Vehicles");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.Business Management");
    }
}
