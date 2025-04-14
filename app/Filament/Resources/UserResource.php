<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $navigationIcon = "gmdi-group-o";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->columns()->schema([
            Forms\Components\Section::make()
                ->columnSpan(1)
                ->schema([
                    TextInput::make("name")
                        ->required()
                        ->columnSpan(1)
                        ->label(__("dashboard.name")),
                    TextInput::make("email")
                        ->required()
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1)
                        ->label(__("dashboard.email")),

                    TextInput::make("password")
                        ->label(__("dashboard.Password"))
                        ->required(fn($operation) => $operation === "create")
                        ->password()
                        ->columnSpan(1)
                        ->revealable(),

                    Forms\Components\Select::make("roles")
                        ->relationship("roles", "name")
                        ->getOptionLabelFromRecordUsing(
                            fn(Model $record) => Str::replace(
                                "_",
                                " ",
                                Str::title($record->name)
                            )
                        ),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->label(__("dashboard.Admin"))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("email")
                    ->label(__("dashboard.email"))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("created_at")
                    ->label(__("dashboard.Created At"))
                    ->dateTime("M j, Y")
                    ->sortable(),
            ])
            ->defaultSort("created_at", "desc")
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            "index" => Pages\ListUsers::route("/"),
            "create" => Pages\CreateUser::route("/create"),
            "edit" => Pages\EditUser::route("/{record}/edit"),
        ];
    }

    public static function getLabel(): ?string
    {
        return __("dashboard.Admin");
    }

    public static function getModelLabel(): string
    {
        return __("dashboard.Admin");
    }

    public static function getNavigationLabel(): string
    {
        return __("dashboard.Admins");
    }

    public static function getPluralLabel(): ?string
    {
        return __("dashboard.Admins");
    }

    public static function getPluralModelLabel(): string
    {
        return __("dashboard.Admins");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.System Settings");
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ["name", "email"];
    }
}
