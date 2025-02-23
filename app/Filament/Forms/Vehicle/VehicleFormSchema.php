<?php

namespace App\Filament\Forms\Vehicle;

use App\Enums\TypesEnum;
use App\Enums\Vehicle\FuelType;
use App\Enums\Vehicle\GearboxType;
use App\Models\Driver;
use App\Models\Vehicle;
use Filament\Forms;

class VehicleFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->columnSpan(
                    fn(string $operation) => $operation == "edit" ? 2 : 3
                )
                ->columns()
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__("dashboard.info"))
                        ->icon("gmdi-info-o")
                        ->schema(self::basicInformationSchema()),

                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.specifications")
                    )
                        ->icon("gmdi-settings-o")
                        ->schema([
                            Forms\Components\Group::make()->schema(
                                self::specificationsSchema()
                            ),
                        ]),

                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.additional_information")
                    )
                        ->icon("gmdi-description-o")
                        ->schema([
                            Forms\Components\Group::make()->schema(
                                self::additionalInformationSchema()
                            ),
                        ]),
                ]),

            self::statusSchema(),
        ];
    }

    private static function basicInformationSchema(): array
    {
        return [
            Forms\Components\Select::make("driver_id")
                ->label(__("dashboard.Driver"))
                ->relationship("driver", "first_name", function ($query) {
                    return $query->orderBy("first_name");
                })
                ->getOptionLabelFromRecordUsing(
                    fn(
                        Driver $record
                    ) => "$record->first_name $record->last_name"
                )
                ->searchable(["first_name", "last_name"])
                ->preload()
                ->nullable(),
            Forms\Components\TextInput::make("name")
                ->label(__("dashboard.name"))
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make("types")
                ->label(__("dashboard.types"))
                ->multiple()
                ->relationship(
                    "types",
                    "name",
                    fn($query) => $query->where(
                        "type",
                        TypesEnum::VEHICLE->value
                    )
                )
                ->preload()
                ->required(),

            Forms\Components\TextInput::make("model")
                ->label(__("dashboard.model"))
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make("license_plate")
                ->label(__("dashboard.license_plate"))
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\DatePicker::make("registration_expiry_date")
                ->label(__("dashboard.registration_expiry_date"))
                ->required(),

            Forms\Components\TextInput::make("daily_rate")
                ->label(__("dashboard.daily_rate"))
                ->required()
                ->numeric()
                ->prefix('$'),

            Forms\Components\DatePicker::make("year_of_first_immatriculation")
                ->label(__("dashboard.year_of_first_immatriculation"))
                ->required(),
        ];
    }

    private static function specificationsSchema(): array
    {
        return [
            Forms\Components\TextInput::make("engine_number")
                ->label(__("dashboard.engine_number"))
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\TextInput::make("engine_type")
                ->label(__("dashboard.engine_type"))
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make("gearbox")
                ->label(__("dashboard.gearbox"))
                ->options(GearboxType::class)
                ->required(),

            Forms\Components\Select::make("fuel_type")
                ->label(__("dashboard.fuel_type"))
                ->options(FuelType::class)
                ->required(),

            Forms\Components\TextInput::make("number_of_seats")
                ->label(__("dashboard.number_of_seats"))
                ->required()
                ->numeric()
                ->minValue(1),

            Forms\Components\TextInput::make("kilometer")
                ->label(__("dashboard.kilometer"))
                ->required()
                ->numeric()
                ->minValue(0),

            Forms\Components\TagsInput::make("options")
                ->label(__("dashboard.options"))
                ->separator(","),
        ];
    }

    private static function additionalInformationSchema(): array
    {
        return [
            Forms\Components\FileUpload::make("document")
                ->label(__("dashboard.document"))
                ->directory("vehicle-documents")
                ->downloadable()
                ->openable()
                ->previewable(),

            Forms\Components\Textarea::make("notes")
                ->label(__("dashboard.notes"))
                ->maxLength(65535),
        ];
    }

    public static function statusSchema(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__("dashboard.Status"))
            ->schema([
                Forms\Components\Placeholder::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->content(
                        fn(?Vehicle $record): string => $record
                            ? $record->created_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->content(
                        fn(?Vehicle $record): string => $record
                            ? $record->updated_at->diffForHumans()
                            : "-"
                    ),
            ])
            ->hidden(fn(string $operation) => $operation !== "edit")
            ->columnSpan(1);
    }
}
