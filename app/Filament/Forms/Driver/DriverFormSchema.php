<?php

namespace App\Filament\Forms\Driver;

use App\Enums\Gender;
use App\Models\Driver;
use Filament\Forms;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class DriverFormSchema
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
                        ->schema(self::personalInformationSchema()),

                    Forms\Components\Tabs\Tab::make(__("dashboard.license_tab"))
                        ->icon("gmdi-badge-o")
                        ->schema([
                            Forms\Components\Group::make()->schema(
                                self::licenseInformationSchema()
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

    private static function personalInformationSchema(): array
    {
        return [
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
        ];
    }

    private static function licenseInformationSchema(): array
    {
        return [
            Forms\Components\TextInput::make("license_number")
                ->label(__("dashboard.license_number"))
                ->required()
                ->maxLength(255),
            Forms\Components\DatePicker::make("issue_date")
                ->label(__("dashboard.issue_date"))
                ->required(),
            Forms\Components\DatePicker::make("expiration_date")
                ->label(__("dashboard.expiration_date"))
                ->required(),
            Forms\Components\FileUpload::make("license")
                ->label(__("dashboard.license"))
                ->directory("driver-licenses")
                ->downloadable()
                ->previewable()
                ->required(),
        ];
    }

    private static function additionalInformationSchema(): array
    {
        return [
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
        ];
    }

    public static function statusSchema(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__("dashboard.Status"))
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
            ->hidden(fn(string $operation) => $operation !== "edit")
            ->columnSpan(1);
    }
}
