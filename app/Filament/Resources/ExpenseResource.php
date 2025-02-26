<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Expense\ExpenseFormSchema;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Filament\Tables\Expense\ExpenseTableSchema;
use App\Models\Expense;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = "gmdi-receipt-o";

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema(ExpenseFormSchema::schema());
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return ExpenseTableSchema::schema($table);
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
            "index" => Pages\ListExpenses::route("/"),
            "create" => Pages\CreateExpense::route("/create"),
            "edit" => Pages\EditExpense::route("/{record}/edit"),
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
        return __("dashboard.Expenses");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.Expense");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.Expenses");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.Business Management");
    }

    public static function getLabel(): ?string
    {
        return __("dashboard.Expense");
    }

    public static function getPluralLabel(): ?string
    {
        return __("dashboard.Expenses");
    }
}
