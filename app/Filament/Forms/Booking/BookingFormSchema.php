<?php

namespace App\Filament\Forms\Booking;

use App\Enums\ReservationStatus;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class BookingFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->columnSpan(
                    fn(string $operation) => $operation == "create" ? 3 : 2
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
                    // TODO: show phone number also not just the customer name in select
                    Forms\Components\Select::make("customers")
                        ->label(__("dashboard.Customer"))
                        ->relationship("customers", "name")
                        ->preload()
                        ->searchable(["name", "email", "phone_number"])
                        ->createOptionForm([
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
                                ->maxLength(1000),
                        ])
                        ->required()
                        ->helperText(__("dashboard.Select or Create Customer")),

                    // Display the selected customers if in edit mode
                    Forms\Components\Placeholder::make("customer_list")
                        ->label(__("dashboard.selected_customers"))
                        ->content(function (
                            callable $get,
                            callable $set,
                            ?Booking $record
                        ) {
                            if (!$record) {
                                return "";
                            }

                            $customerList = "";
                            foreach ($record->customers as $customer) {
                                $customerList .= "• $customer->name ($customer->phone_number)<br>";
                            }

                            return new HtmlString($customerList);
                        })
                        ->visible(
                            fn(string $operation): bool => $operation === "edit"
                        ),
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
                    Forms\Components\TextInput::make("reference_number")
                        ->label(__("dashboard.reference_number"))
                        ->disabled()
                        ->placeholder("Will be auto-generated")
                        ->maxLength(255),
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
                        ->options(ReservationStatus::class)
                        ->default(ReservationStatus::Confirmed)
                        ->visible(fn($operation) => $operation === "create")
                        ->required(),
                    MoneyInput::make("total_price")->required(),
                ])
                ->columnSpan(["sm" => 2, "md" => 2, "lg" => 1, "xl" => 1])
                ->columns(1),

            Forms\Components\Section::make()
                ->extraAttributes([
                    "class" => "booking",
                ])
                ->columnSpan(["sm" => 2, "md" => 2, "lg" => 1, "xl" => 1])
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
                ->heading(__("dashboard.reservation_period")),
            Forms\Components\Group::make()
                ->schema([
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
                ->columnSpan(2)
                ->columns(),
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
                ->preload(),
            Forms\Components\Textarea::make("notes")
                ->label(__("dashboard.notes"))
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
                __("dashboard.booking_details")
            )->schema([
                Forms\Components\Placeholder::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->inlineLabel()
                    ->content(
                        fn(?Booking $record): string => $record
                            ? $record->created_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->inlineLabel()
                    ->content(
                        fn(?Booking $record): string => $record
                            ? $record->updated_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("duration")
                    ->label(__("dashboard.duration"))
                    ->inlineLabel()
                    ->content(
                        fn(?Booking $record): string => $record
                            ? $record->duration_in_days .
                                " " .
                                __("dashboard.days")
                            : "-"
                    )
                    ->hidden(fn(string $operation) => $operation === "create"),
            ]),
        ])
            ->hidden(fn(string $operation) => $operation === "create")
            ->columnSpan(["lg" => 1]);
    }
}
