<?php

namespace App\Filament\Forms\Shipping;

use App\Enums\Shipping\ShippingStatus;
use App\Models\Shipping;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ShippingFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Group::make([
                Forms\Components\Tabs::make()->tabs([
                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.shipping_details")
                    )
                        ->icon("gmdi-local-shipping-o")
                        ->schema([
                            Forms\Components\Group::make()
                                ->schema(self::shippingDetailsSchema())
                                ->columns(),
                        ]),
                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.client_information")
                    )
                        ->icon("gmdi-person-o")
                        ->schema(self::clientInformationSchema())
                        ->columns(),
                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.delivery_information")
                    )
                        ->icon("gmdi-local-shipping-o")
                        ->schema(self::deliveryInformationSchema())
                        ->columns(),
                ]),
                self::getItemsRepeater(),
            ])->columnSpan(
                fn(string $operation) => $operation == "edit" ? 2 : 3
            ),

            self::statusInfoSection(),
        ];
    }

    private static function shippingDetailsSchema(): array
    {
        return [
            Forms\Components\Group::make([
                Forms\Components\TextInput::make("reference_number")
                    ->label(__("dashboard.tracking_number"))
                    ->disabled()
                    ->placeholder("Will be auto-generated")
                    ->maxLength(255),

                Forms\Components\Select::make("driver_id")
                    ->label(__("dashboard.Driver"))
                    ->relationship("driver", "first_name")
                    ->getOptionLabelFromRecordUsing(
                        fn($record) => "$record->first_name $record->last_name"
                    )
                    ->searchable(["first_name", "last_name"])
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make("status")
                    ->label(__("dashboard.status"))
                    ->options(ShippingStatus::class)
                    ->default(ShippingStatus::Pending)
                    ->required(),

                Forms\Components\TextInput::make("total_weight")
                    ->label(__("dashboard.total_weight"))
                    ->disabled()
                    ->numeric()
                    ->suffix("kg")
                    ->placeholder("Calculated from items"),
            ])->columnSpan(1),

            Forms\Components\Group::make([
                Forms\Components\DateTimePicker::make("pick_up_at")
                    ->label(__("dashboard.Pickup at"))
                    ->seconds(false),
                Forms\Components\Textarea::make("pickup_address")
                    ->label(__("dashboard.pickup_address"))
                    ->rows(3)
                    ->required()
                    ->maxLength(65535),

                Forms\Components\Textarea::make("delivery_address")
                    ->label(__("dashboard.delivery_address"))
                    ->rows(3)
                    ->required()
                    ->maxLength(65535),
            ])->columnSpan(1),
        ];
    }

    private static function clientInformationSchema(): array
    {
        return [
            Forms\Components\Group::make()
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
                        ->required(),
                ])
                ->columnSpan(1),
            Forms\Components\Textarea::make("notes")
                ->label(__("dashboard.notes"))
                ->rows(5)
                ->maxLength(65535)
                ->columnSpan(1),
        ];
    }

    private static function deliveryInformationSchema(): array
    {
        return [
            Forms\Components\Group::make([
                Forms\Components\DateTimePicker::make("received_at")
                    ->label(__("dashboard.received_at"))
                    ->seconds(false),

                Forms\Components\DateTimePicker::make("delivered_at")
                    ->label(__("dashboard.delivered_at"))
                    ->seconds(false)
                    ->after("received_at"),

                Forms\Components\Textarea::make("delivery_notes")
                    ->label(__("dashboard.delivery_notes"))
                    ->rows(3)
                    ->maxLength(65535),
            ])->columns(1),
        ];
    }

    /**
     * @return Forms\Components\Section
     */
    public static function getItemsRepeater(): Forms\Components\Section
    {
        return Forms\Components\Section::make(
            __("dashboard.shipping_items")
        )->schema([
            TableRepeater::make("items")
                ->relationship("items")
                ->hiddenLabel()
                ->headers([
                    Header::make(__("dashboard.name"))->markAsRequired(),
                    Header::make(__("dashboard.quantity"))->markAsRequired(),
                    Header::make(__("dashboard.weight"))->markAsRequired(),
                ])
                ->schema([
                    Forms\Components\TextInput::make("name")
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make("quantity")
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(),

                    Forms\Components\TextInput::make("weight")
                        ->numeric()
                        ->minValue(0)
                        ->suffix("kg")
                        ->required(),
                ])
                ->columns(3)
                ->itemLabel(fn(array $state): ?string => $state["name"] ?? null)
                ->addActionLabel(__("dashboard.add_item"))
                ->defaultItems(0)
                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                    // This will be used to trigger a recalculation in the save handler
                    $set("_items_updated", true);
                }),
        ]);
    }

    public static function statusInfoSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(
            __("dashboard.shipping_status_info")
        )
            ->schema([
                Forms\Components\Placeholder::make("created_at")
                    ->inlineLabel()
                    ->label(__("dashboard.created_at"))
                    ->content(
                        fn(?Shipping $record): string => $record
                            ? $record->created_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->inlineLabel()
                    ->content(
                        fn(?Shipping $record): string => $record
                            ? $record->updated_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("items_count")
                    ->label(__("dashboard.items"))
                    ->inlineLabel()
                    ->content(
                        fn(?Shipping $record): string => $record
                            ? $record->items()->count() .
                                " " .
                                __("dashboard.items")
                            : "0 " . __("dashboard.items")
                    )
                    ->hidden(fn(string $operation) => $operation !== "edit"),

                Forms\Components\Placeholder::make("total_weight")
                    ->inlineLabel()
                    ->content(
                        fn(?Shipping $record) => $record
                            ? $record->total_weight . " kg"
                            : "0 kg"
                    )
                    ->label(__("dashboard.total_weight"))
                    ->hidden(fn(string $operation) => $operation !== "edit"),
            ])
            ->hidden(fn(string $operation) => $operation !== "edit")
            ->columnSpan(["lg" => 1]);
    }
}
