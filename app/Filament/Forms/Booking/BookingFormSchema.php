<?php

namespace App\Filament\Forms\Booking;

use App\Enums\Booking\BookingStatus;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Get;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class BookingFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->activeTab(2)
                ->columnSpan(
                    fn(string $operation) => $operation == "edit" ? 2 : 3
                )
                ->columns()
                ->tabs([
                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.client_information")
                    )
                        ->icon("gmdi-person-o")
                        ->schema(self::clientInformationSchema()),

                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.booking_details")
                    )
                        ->icon("gmdi-book-o")
                        ->schema(self::bookingDetailsSchema()),

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

    private static function clientInformationSchema(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TextInput::make("client_name")
                        ->label(__("dashboard.name"))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make("client_email")
                        ->label(__("dashboard.email"))
                        ->email()
                        ->maxLength(255),
                    PhoneInput::make("client_phone")
                        ->label(__("dashboard.phone_number"))
                        ->unique(ignoreRecord: true)
                        ->required(),
                ])
                ->columns(1)
                ->columnSpan(1),
        ];
    }

    private static function bookingDetailsSchema(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Select::make("vehicle_id")
                        ->label(__("dashboard.Vehicle"))
                        ->relationship("vehicle", "name", function ($query) {
                            return $query->orderBy("name");
                        })
                        ->preload()
                        ->searchable(["name", "model", "license_plate"])
                        ->required()
                        ->live()
                        ->afterStateUpdated(
                            fn(
                                Forms\Get $get,
                                Forms\Set $set,
                                ?string $state
                            ) => self::updateDriverInfo($get, $set, $state)
                        ),
                    Forms\Components\Select::make("driver_id")
                        ->label(__("dashboard.Driver"))
                        ->relationship("driver", "first_name")
                        ->live()
                        ->getOptionLabelFromRecordUsing(
                            fn(Driver $record) => "$record->full_name"
                        )
                        ->searchable([
                            "first_name",
                            "last_name",
                            "license_number",
                        ])
                        ->preload(),

                    Forms\Components\Select::make("status")
                        ->label(__("dashboard.status"))
                        ->options(BookingStatus::class)
                        ->default(BookingStatus::Pending)
                        ->required(),
                    Forms\Components\Textarea::make("pickup_address")
                        ->label(__("dashboard.pickup_address"))
                        ->rows(3)
                        ->required()
                        ->maxLength(65535),
                    Forms\Components\Textarea::make("destination_address")
                        ->label(__("dashboard.destination_address"))
                        ->rows(3)
                        ->maxLength(65535),
                ])
                ->columnSpan(1)
                ->columns(1),

            Forms\Components\Section::make()
                ->extraAttributes([
                    "class" => "booking",
                ])
                ->schema([
                    Forms\Components\DateTimePicker::make("start_datetime")
                        ->label(__("dashboard.start_datetime"))
                        ->seconds(false)
                        ->required(),

                    Forms\Components\DateTimePicker::make("end_datetime")
                        ->label(__("dashboard.end_datetime"))
                        ->seconds(false)
                        ->required()
                        ->afterOrEqual(fn(Get $get) => $get("start_datetime")),
                ])
                ->columnSpan(1)
                ->heading(__("dashboard.reservation_period")),
        ];
    }

    private static function updateDriverInfo(
        Forms\Get $get,
        Forms\Set $set,
        ?string $vehicleId
    ): void {
        if (!$vehicleId) {
            return;
        }

        // If vehicle has a driver assigned, preselect that driver
        $vehicle = Vehicle::find($vehicleId);
        if ($vehicle && $vehicle->driver_id) {
            $set("driver_id", $vehicle->driver_id);
        }
    }

    private static function additionalInformationSchema(): array
    {
        return [
            Forms\Components\Select::make("addons")
                ->label(__("dashboard.Addons"))
                ->multiple()
                ->relationship("addons", "name")
                ->preload()
                ->required(),

            Forms\Components\Textarea::make("notes")
                ->label(__("dashboard.notes"))
                ->maxLength(65535),
        ];
    }

    public static function statusInfoSection(): Forms\Components\Section
    {
        //        TODO: add reactive price (all section details )
        return Forms\Components\Section::make(
            __("dashboard.booking_status_info")
        )
            ->schema([
                Forms\Components\Placeholder::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->content(
                        fn(?Booking $record): string => $record
                            ? $record->created_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->content(
                        fn(?Booking $record): string => $record
                            ? $record->updated_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("duration")
                    ->label(__("dashboard.duration"))
                    ->content(
                        fn(?Booking $record): string => $record
                            ? $record->duration_in_days .
                                " " .
                                __("dashboard.days")
                            : "-"
                    )
                    ->hidden(fn(string $operation) => $operation !== "edit"),

                Forms\Components\Placeholder::make("total_price_money")
                    ->content(
                        fn(?Booking $record) => $record->formatted_total_price
                    )
                    ->label(__("dashboard.total_price"))
                    ->hidden(fn(string $operation) => $operation !== "edit"),
            ])
            ->hidden(fn(string $operation) => $operation !== "edit")
            ->columnSpan(["lg" => 1]);
    }
}
