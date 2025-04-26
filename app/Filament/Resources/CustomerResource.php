<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers\BookingsRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\RentsRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\ShippingsRelationManager;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = "heroicon-o-users";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make("name")
                        ->label(__("dashboard.name"))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make("email")
                        ->label(__("dashboard.email"))
                        ->email()
                        ->maxLength(255),

                    PhoneInput::make("phone_number")
                        ->label(__("dashboard.phone_number"))
                        ->required(),

                    Forms\Components\Textarea::make("notes")
                        ->label(__("dashboard.notes"))
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->label(__("dashboard.name"))
                    ->searchable()
                    ->sortable(),

                PhoneColumn::make("phone_number")
                    ->label(__("dashboard.phone_number"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("email")
                    ->label(__("dashboard.email"))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make("bookings_count")
                    ->label(__("dashboard.Bookings"))
                    ->counts("bookings")
                    ->sortable(),

                Tables\Columns\TextColumn::make("rents_count")
                    ->label(__("dashboard.Rents"))
                    ->counts("rents")
                    ->sortable(),

                Tables\Columns\TextColumn::make("shippings_count")
                    ->label(__("dashboard.Shippings"))
                    ->counts("shippings")
                    ->sortable(),

                Tables\Columns\TextColumn::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->dateTime("M j, Y")
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->dateTime("M j, Y")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([Tables\Filters\TrashedFilter::make()])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
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
