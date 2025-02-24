<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Booking\BookingFormSchema;
use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Filament\Tables\Booking\BookingTableSchema;
use App\Models\Booking;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = "gmdi-event-o";

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema(BookingFormSchema::schema());
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return BookingTableSchema::schema($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListBookings::route("/"),
            "create" => Pages\CreateBooking::route("/create"),
            "edit" => Pages\EditBooking::route("/{record}/edit"),
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
        return __("dashboard.Bookings");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.Booking");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.Bookings");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.Business Management");
    }

    public static function getLabel(): ?string
    {
        return __("dashboard.Booking");
    }

    public static function getPluralLabel(): ?string
    {
        return __("dashboard.Bookings");
    }
}
