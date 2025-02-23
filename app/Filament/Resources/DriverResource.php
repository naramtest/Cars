<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = "gmdi-sports-motorsports-o";

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema([
            Forms\Components\Tabs::make()
                ->columnSpan(function (string $operation) {
                    return $operation == "edit" ? 2 : 3;
                })
                ->columns()
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__("dashboard.info"))
                        ->icon("gmdi-info-o")
                        ->schema([
                            Forms\Components\TextInput::make("first_name")
                                ->label(__("dashboard.first_name"))
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make("last_name")
                                ->label(__("dashboard.last_name"))
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make("email")
                                ->label(__("dashboard.email"))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            PhoneInput::make("phone_number")
                                ->label(__("dashboard.phone_number"))
                                ->required(),

                            Forms\Components\Select::make("gender")
                                ->label(__("dashboard.gender"))
                                ->options(Gender::class)
                                ->required(),
                            Forms\Components\DatePicker::make("birth_date")
                                ->label(__("dashboard.birth_date"))
                                ->required(),
                            Forms\Components\Textarea::make("address")
                                ->label(__("dashboard.address"))
                                ->required()
                                ->maxLength(65535),
                        ]),

                    Forms\Components\Tabs\Tab::make(__("dashboard.license_tab"))
                        ->icon("gmdi-badge-o")
                        ->schema([
                            Forms\Components\Group::make()->schema([
                                Forms\Components\TextInput::make(
                                    "license_number"
                                )
                                    ->label(__("dashboard.license_number"))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make("issue_date")
                                    ->label(__("dashboard.issue_date"))
                                    ->required(),
                                Forms\Components\DatePicker::make(
                                    "expiration_date"
                                )
                                    ->label(__("dashboard.expiration_date"))
                                    ->required(),
                                Forms\Components\FileUpload::make("license")
                                    ->label(__("dashboard.license"))
                                    ->directory("driver-licenses")
                                    ->downloadable()
                                    ->previewable()
                                    ->required(),
                            ]),
                        ]),
                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.additional_information")
                    )
                        ->icon("gmdi-description-o")
                        ->schema([
                            Forms\Components\Group::make()->schema([
                                Forms\Components\FileUpload::make("document")
                                    ->label(__("dashboard.document"))
                                    ->directory("driver-documents")
                                    ->downloadable()
                                    ->previewable()
                                    ->required(),
                                Forms\Components\TextInput::make("reference")
                                    ->label(__("dashboard.reference"))
                                    ->maxLength(255),
                                Forms\Components\Textarea::make("notes")
                                    ->label(__("dashboard.notes"))
                                    ->maxLength(65535),
                            ]),
                        ]),
                ]),
            Forms\Components\Section::make(__("dashboard.Status"))
                ->schema([
                    Forms\Components\Placeholder::make("created_at")
                        ->label(__("dashboard.created_at"))
                        ->content(
                            fn(?Driver $record): string => $record
                                ? $record->created_at->diffForHumans()
                                : "-"
                        ),

                    Forms\Components\Placeholder::make("updated_at")
                        ->label(__("dashboard.updated_at"))
                        ->content(
                            fn(?Driver $record): string => $record
                                ? $record->updated_at->diffForHumans()
                                : "-"
                        ),
                ])
                ->columnSpan(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([Tables\Actions\EditAction::make()])
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
            "index" => Pages\ListDrivers::route("/"),
            "create" => Pages\CreateDriver::route("/create"),
            "edit" => Pages\EditDriver::route("/{record}/edit"),
        ];
    }
}
