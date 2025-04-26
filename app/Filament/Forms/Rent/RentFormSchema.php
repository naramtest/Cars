<?php

namespace App\Filament\Forms\Rent;

use App\Enums\ReservationStatus;
use App\Filament\Component\Customer\CustomerFormComponent;
use App\Models\Rent;
use Filament\Forms;
use Filament\Forms\Get;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class RentFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->columnSpan(
                    fn(string $operation) => $operation == "edit" ? 2 : 3
                )
                ->tabs([
                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.client_information")
                    )
                        ->icon("gmdi-person-o")
                        ->schema(
                            CustomerFormComponent::clientInformationSchema()
                        ),

                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.rent_details")
                    )
                        ->icon("gmdi-directions-car-o")
                        ->schema([
                            Forms\Components\Group::make()
                                ->schema(self::rentDetailsSchema())
                                ->columns(),
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

            self::statusInfoSection(),
        ];
    }

    private static function rentDetailsSchema(): array
    {
        return [
            Forms\Components\Group::make([
                Forms\Components\TextInput::make("reference_number")
                    ->label(__("dashboard.reference_number"))
                    ->disabled()
                    ->placeholder("Will be auto-generated")
                    ->maxLength(255),

                Forms\Components\Select::make("vehicle_id")
                    ->label(__("dashboard.Vehicle"))
                    ->relationship("vehicle", "name")
                    ->searchable(["name", "model", "license_plate"])
                    ->preload()
                    ->required(),

                Forms\Components\Select::make("status")
                    ->label(__("dashboard.status"))
                    ->options(ReservationStatus::class)
                    ->default(ReservationStatus::Confirmed)
                    ->visible(fn($operation) => $operation === "create")
                    ->required(),
                MoneyInput::make("total_price")
                    ->required()
                    ->label(__("dashboard.total_price")),
            ])->columnSpan(1),
            Forms\Components\Section::make()
                ->extraAttributes([
                    "class" => "rent",
                ])
                ->schema([
                    Forms\Components\DateTimePicker::make("rental_start_date")
                        ->label(__("dashboard.start_datetime"))
                        ->seconds(false)
                        ->required(),

                    Forms\Components\DateTimePicker::make("rental_end_date")
                        ->label(__("dashboard.end_datetime"))
                        ->seconds(false)
                        ->afterOrEqual(
                            fn(Get $get) => $get("rental_start_date")
                        ),
                ])
                ->heading(__("dashboard.reservation_period"))
                ->columnSpan(1),

            Forms\Components\Textarea::make("pickup_address")
                ->label(__("dashboard.pickup_address"))
                ->rows(3)
                ->required()
                ->maxLength(65535),

            Forms\Components\Textarea::make("drop_off_address")
                ->label(__("dashboard.drop_off_address"))
                ->rows(3)
                ->required()
                ->maxLength(65535),
        ];
    }

    private static function additionalInformationSchema(): array
    {
        return [
            Forms\Components\Textarea::make("terms_conditions")
                ->label(__("dashboard.terms_conditions"))
                ->rows(3)
                ->maxLength(65535),

            Forms\Components\Textarea::make("description")
                ->label(__("dashboard.description"))
                ->rows(3)
                ->maxLength(65535),
        ];
    }

    public static function statusInfoSection(): Forms\Components\Group
    {
        return Forms\Components\Group::make([
            Forms\Components\Section::make(__("dashboard.Status"))->schema([
                Forms\Components\Select::make("status")
                    ->hiddenLabel()
                    ->options(ReservationStatus::class)
                    ->default(ReservationStatus::Confirmed)
                    ->required(),
            ]),
            Forms\Components\Section::make(
                __("dashboard.rent_details")
            )->schema([
                Forms\Components\Placeholder::make("created_at")
                    ->inlineLabel()
                    ->label(__("dashboard.created_at"))
                    ->content(
                        fn(?Rent $record): string => $record
                            ? $record->created_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->inlineLabel()
                    ->content(
                        fn(?Rent $record): string => $record
                            ? $record->updated_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("duration")
                    ->label(__("dashboard.duration"))
                    ->inlineLabel()
                    ->content(
                        fn(?Rent $record): string => $record &&
                        $record->rental_end_date
                            ? $record->duration_in_days .
                                " " .
                                __("dashboard.days")
                            : "-"
                    )
                    ->hidden(fn(string $operation) => $operation !== "edit"),
            ]),
        ])
            ->hidden(fn(string $operation) => $operation !== "edit")
            ->columnSpan(["lg" => 1]);
    }
}
