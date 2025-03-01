<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Shipping\ShippingFormSchema;
use App\Filament\Resources\ShippingResource\Pages;
use App\Filament\Tables\Shipping\ShippingTableSchema;
use App\Models\Shipping;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShippingResource extends Resource
{
    protected static ?string $model = Shipping::class;

    protected static ?string $navigationIcon = "gmdi-local-shipping-o";

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema(ShippingFormSchema::schema());
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return ShippingTableSchema::schema($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListShippings::route("/"),
            "create" => Pages\CreateShipping::route("/create"),
            "edit" => Pages\EditShipping::route("/{record}/edit"),
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
        return __("dashboard.Shippings");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.Shipping");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.Shippings");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.Business Management");
    }

    public static function getLabel(): ?string
    {
        return __("dashboard.Shipping");
    }

    public static function getPluralLabel(): ?string
    {
        return __("dashboard.Shippings");
    }
}
