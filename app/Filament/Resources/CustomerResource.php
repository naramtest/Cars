<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Customer\CustomerFormSchema;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers\BookingsRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\RentsRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\ShippingsRelationManager;
use App\Filament\Tables\Customer\CustomerTableSchema;
use App\Models\Customer;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = "heroicon-o-users";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return CustomerFormSchema::schema($form);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return CustomerTableSchema::schema($table);
    }

    public static function getRelations(): array
    {
        return [
            BookingsRelationManager::class,
            RentsRelationManager::class,
            ShippingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListCustomers::route("/"),
            "create" => Pages\CreateCustomer::route("/create"),
            "view" => Pages\ViewCustomer::route("/{record}"),
            "edit" => Pages\EditCustomer::route("/{record}/edit"),
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
        return __("dashboard.Customers");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.Customer");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.Customers");
    }

    public static function getLabel(): ?string
    {
        return __("dashboard.Customer");
    }

    public static function getPluralLabel(): ?string
    {
        return __("dashboard.Customers");
    }
}
