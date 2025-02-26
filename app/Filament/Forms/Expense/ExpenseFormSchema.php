<?php

namespace App\Filament\Forms\Expense;

use App\Enums\TypesEnum;
use App\Models\Expense;
use Filament\Forms;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class ExpenseFormSchema
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
            Forms\Components\TextInput::make("title")
                ->label(__("dashboard.title"))
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make("vehicle_id")
                ->label(__("dashboard.Vehicle"))
                ->relationship("vehicle", "name", function ($query) {
                    return $query->orderBy("name");
                })
                ->searchable(["name", "model", "license_plate"])
                ->preload()
                ->nullable(),

            Forms\Components\Select::make("types")
                ->label(__("dashboard.types"))
                ->multiple()
                ->relationship(
                    "types",
                    "name",
                    fn($query) => $query->where(
                        "type",
                        TypesEnum::EXPENSE->value
                    )
                )
                ->preload()
                ->required(),

            Forms\Components\DatePicker::make("expense_date")
                ->label(__("dashboard.expense_date"))
                ->required(),

            MoneyInput::make("amount")
                ->label(__("dashboard.amount"))
                ->required(),
        ];
    }

    private static function additionalInformationSchema(): array
    {
        return [
            Forms\Components\FileUpload::make("receipt")
                ->label(__("dashboard.receipt"))
                ->directory("expense-receipts")
                ->downloadable()
                ->openable()
                ->previewable()
                ->nullable(),

            Forms\Components\Textarea::make("notes")
                ->label(__("dashboard.notes"))
                ->maxLength(65535)
                ->nullable(),
        ];
    }

    public static function statusSchema(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__("dashboard.Status"))
            ->schema([
                Forms\Components\Placeholder::make("created_at")
                    ->label(__("dashboard.created_at"))
                    ->content(
                        fn(?Expense $record): string => $record
                            ? $record->created_at->diffForHumans()
                            : "-"
                    ),

                Forms\Components\Placeholder::make("updated_at")
                    ->label(__("dashboard.updated_at"))
                    ->content(
                        fn(?Expense $record): string => $record
                            ? $record->updated_at->diffForHumans()
                            : "-"
                    ),
            ])
            ->hidden(fn(string $operation) => $operation !== "edit")
            ->columnSpan(1);
    }
}
