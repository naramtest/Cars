<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Inspection\InspectionFormSchema;
use App\Filament\Resources\InspectionResource\Pages;
use App\Filament\Resources\InspectionResource\RelationManagers;
use App\Filament\Tables\Inspection\InspectionTableSchema;
use App\Models\Inspection;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = "heroicon-o-clipboard-document-check";

    public static function form(Form $form): Form
    {
        return $form->schema(InspectionFormSchema::schema());
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return InspectionTableSchema::schema($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListInspections::route("/"),
            "create" => Pages\CreateInspection::route("/create"),
            "edit" => Pages\EditInspection::route("/{record}/edit"),
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
        return __("dashboard.Inspections");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.Inspection");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.Inspections");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.Business Management");
    }

    public static function getLabel(): ?string
    {
        return __("dashboard.Inspection");
    }

    public static function getPluralLabel(): ?string
    {
        return __("dashboard.Inspections");
    }
}
