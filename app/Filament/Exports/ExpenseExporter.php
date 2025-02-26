<?php

namespace App\Filament\Exports;

use App\Models\Expense;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ExpenseExporter extends Exporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make("id")->label("ID"),
            ExportColumn::make("title")->label(__("dashboard.title")),
            ExportColumn::make("vehicle.name")->label(__("dashboard.Vehicle")),
            ExportColumn::make("expense_date")->label(
                __("dashboard.expense_date")
            ),
            ExportColumn::make("amount")
                ->label(__("dashboard.amount"))
                ->formatStateUsing(
                    fn(Expense $expense) => $expense->formatted_amount
                ),
            ExportColumn::make("currency_code")->label(
                __("dashboard.currency")
            ),
            ExportColumn::make("types")
                ->label(__("dashboard.types"))
                ->formatStateUsing(
                    fn(Expense $expense) => $expense->types
                        ->pluck("name")
                        ->implode(", ")
                ),
            ExportColumn::make("notes")->label(__("dashboard.notes")),
            ExportColumn::make("created_at")->label(__("dashboard.created_at")),
            ExportColumn::make("updated_at")->label(__("dashboard.updated_at")),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body =
            "Your expense export has completed and " .
            number_format($export->successful_rows) .
            " " .
            str("row")->plural($export->successful_rows) .
            " exported.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .=
                " " .
                number_format($failedRowsCount) .
                " " .
                str("row")->plural($failedRowsCount) .
                " failed to export.";
        }

        return $body;
    }

    public function getFormats(): array
    {
        return [ExportFormat::Xlsx];
    }
}
