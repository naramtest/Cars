<?php

namespace App\Filament\Forms\Inspection;

use App\Enums\Inspection\InspectionStatus;
use App\Enums\Inspection\RepairStatus;
use App\Enums\TypesEnum;
use App\Models\Inspection;
use App\Models\Type;
use Filament\Forms;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class InspectionFormSchema
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
                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.inspection_details")
                    )
                        ->icon("heroicon-o-clipboard")
                        ->schema(self::inspectionDetailsSchema()),

                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.incoming_details")
                    )
                        ->icon("heroicon-o-truck")
                        ->schema([
                            Forms\Components\Group::make()->schema(
                                self::incomingDetailsSchema()
                            ),
                        ]),

                    Forms\Components\Tabs\Tab::make(
                        __("dashboard.inspection_checklist")
                    )
                        ->icon("heroicon-o-check-circle")
                        ->schema([
                            Forms\Components\Group::make()->schema(
                                self::checklistSchema()
                            ),
                        ]),
                ]),

            self::statusSchema(),
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
                ->columnSpanFull(),

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
                        ->inline(false),
                    Forms\Components\Textarea::make("checklist.$slug.notes")
                        ->label(__("dashboard.notes"))
                        ->placeholder(__("dashboard.enter_notes"))
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(1);
        }

        return $schema;
    }

    public static function statusSchema(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__("dashboard.Status"))
            ->schema([
                Forms\Components\Placeholder::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->content(
                        fn(?Inspection $record): string => $record
                            ? $record->created_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->content(
                        fn(?Inspection $record): string => $record
                            ? $record->updated_at->diffForHumans()
                            : "-"
                    ),
            ])
            ->hidden(fn(string $operation) => $operation !== "edit")
            ->columnSpan(1);
    }
}
