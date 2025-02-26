<?php

namespace App\Filament\Forms\Inspection;

use App\Enums\Inspection\InspectionStatus;
use App\Enums\Inspection\RepairStatus;
use App\Enums\TypesEnum;
use App\Models\Type;
use Filament\Forms;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class InspectionFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Group::make([
                Forms\Components\Section::make("Details")
                    ->schema(self::inspectionDetailsSchema())
                    ->columns()
                    ->columnSpan(1),
                Forms\Components\Group::make([
                    Forms\Components\Section::make("amount")
                        ->schema(self::amountDetailsSchema())
                        ->columns(),
                    Forms\Components\Section::make(
                        __("dashboard.incoming_details")
                    )
                        ->schema(self::incomingDetailsSchema())
                        ->columns(),
                ])->columnSpan(1),
            ])
                ->columns()
                ->columnSpan(2),
            Forms\Components\Section::make(__("dashboard.inspection_checklist"))
                ->schema(self::checklistSchema())
                ->columns(),
        ];
    }

    private static function inspectionDetailsSchema(): array
    {
        return [
            Forms\Components\Select::make("vehicle_id")
                ->label(__("dashboard.Vehicle"))
                ->relationship("vehicle", "name", function ($query) {
                    return $query->orderBy("name");
                })
                ->searchable(["name", "model", "license_plate"])
                ->preload()
                ->required(),

            Forms\Components\TextInput::make("inspection_by")
                ->label(__("dashboard.inspection_by"))
                ->maxLength(255),

            Forms\Components\DatePicker::make("inspection_date")
                ->label(__("dashboard.inspection_date"))
                ->required(),

            Forms\Components\Select::make("status")
                ->label(__("dashboard.inspection_status"))
                ->options(InspectionStatus::class)
                ->default(InspectionStatus::Pending)
                ->required(),

            Forms\Components\Select::make("repair_status")
                ->label(__("dashboard.repair_status"))
                ->options(RepairStatus::class)
                ->default(RepairStatus::Pending)
                ->required(),

            Forms\Components\Textarea::make("notes")
                ->label(__("dashboard.notes"))
                ->rows(2),
        ];
    }

    private static function amountDetailsSchema(): array
    {
        return [
            MoneyInput::make("amount")
                ->label(__("dashboard.amount"))
                ->nullable(),
            Forms\Components\FileUpload::make("receipt")
                ->label(__("dashboard.receipt"))
                ->directory("inspection-receipts")
                ->downloadable()
                ->openable()
                ->previewable()
                ->nullable(),
        ];
    }

    private static function incomingDetailsSchema(): array
    {
        return [
            Forms\Components\DatePicker::make("incoming_date")
                ->label(__("dashboard.incoming_date"))
                ->nullable(),

            Forms\Components\TextInput::make("meter_reading_km")
                ->label(__("dashboard.meter_reading_km"))
                ->numeric()
                ->minValue(0)
                ->nullable(),
        ];
    }

    private static function checklistSchema(): array
    {
        // Get all inspection types from the database
        $checklistItems = Type::where("type", TypesEnum::INSPECTION->value)
            ->where("is_visible", true)
            ->orderBy("order")
            ->get()
            ->pluck("name", "slug")
            ->toArray();

        $schema = [];

        foreach ($checklistItems as $slug => $name) {
            $schema[] = Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Toggle::make("checklist.$slug.checked")
                        ->label($name)
                        ->inline(false)
                        ->columnSpan(1),
                    Forms\Components\TextInput::make("checklist.$slug.notes")
                        ->label(__("dashboard.notes"))
                        ->placeholder(__("dashboard.enter_notes"))
                        ->columnSpan(4),
                ])
                ->columns(5);
        }

        return $schema;
    }
}
