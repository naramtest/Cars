<?php

namespace App\Filament\Forms\Vehicle;

use App\Enums\TypesEnum;
use App\Enums\Vehicle\FuelType;
use App\Enums\Vehicle\GearboxType;
use App\Models\Driver;
use App\Models\Vehicle;
use Filament\Forms;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class VehicleFormSchema
{
    private static function inspectionPeriodSchema(): array
    {
        return [
            Forms\Components\Section::make(__("dashboard.inspection_period"))
                ->schema([
                    Forms\Components\TextInput::make("inspection_period_days")
                        ->label(__("dashboard.inspection_period_days"))
                        ->numeric()
                        ->hiddenLabel()
                        ->minValue(1)
                        ->nullable()
                        ->suffixIcon("heroicon-m-calendar")
                        ->suffix(__("dashboard.days")),
                    Forms\Components\Toggle::make("notify_before_inspection")
                        ->label(__("dashboard.notify_before_inspection"))
                        ->default(true),
                ])
                ->columns(3),
        ];
    }

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
            ...self::inspectionPeriodSchema(),
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

            MoneyInput::make("daily_rate")
                ->label(__("dashboard.daily_rate"))
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make("year_of_first_immatriculation")
                ->minValue(1900)
                ->label(__("dashboard.year_of_first_immatriculation"))
                ->numeric()
                ->maxValue(now()->year)
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

    public static function statusSchema(): Forms\Components\Group
    {
        return Forms\Components\Group::make()
            ->schema([
                Forms\Components\Section::make("status")->schema([
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
                ]),
                Forms\Components\Section::make("Inspection")->schema([
                    Forms\Components\Placeholder::make("next_inspection_date")
                        ->label("Latest Inspection")
                        ->visible(function (?Vehicle $record): bool {
                            return (bool) $record
                                ->inspections()
                                ->latest()
                                ->first();
                        })
                        ->content(function (?Vehicle $record): string {
                            $inspection = $record
                                ->inspections()
                                ->latest()
                                ->first();
                            return $inspection->inspection_date->format(
                                "M j, Y"
                            ) ?? "-";
                        }),
                    Forms\Components\Placeholder::make("next_inspection_date")
                        ->visible(function (?Vehicle $record): bool {
                            return (bool) $record
                                ->inspections()
                                ->latest()
                                ->first();
                        })
                        ->label(__("dashboard.next_inspection_date"))
                        ->content(function (?Vehicle $record): string {
                            return $record->next_inspection_date->format(
                                "M j, Y"
                            ) ?? "-";
                        }),
                ]),
            ])
            ->hidden(fn(string $operation) => $operation !== "edit")
            ->columnSpan(1);
    }
}
