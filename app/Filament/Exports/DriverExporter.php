<?php

namespace App\Filament\Exports;

use App\Models\Driver;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DriverExporter extends Exporter
{
    protected static ?string $model = Driver::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make("id")->label("ID"),
            ExportColumn::make("first_name")->label(__("dashboard.first_name")),
            ExportColumn::make("last_name")->label(__("dashboard.last_name")),
            ExportColumn::make("email")->label(__("dashboard.email")),
            ExportColumn::make("phone_number")->label(
                __("dashboard.phone_number")
            ),
            ExportColumn::make("gender")
                ->label(__("dashboard.gender"))
                ->formatStateUsing(
                    fn(Driver $driver) => $driver->gender->value
                ),
            ExportColumn::make("birth_date")->label(__("dashboard.birth_date")),
            ExportColumn::make("address")->label(__("dashboard.address")),
            ExportColumn::make("license_number")->label(
                __("dashboard.license_number")
            ),
            ExportColumn::make("issue_date")->label(__("dashboard.issue_date")),
            ExportColumn::make("expiration_date")->label(
                __("dashboard.expiration_date")
            ),
            ExportColumn::make("license_reference")->label(
                __("dashboard.reference")
            ),
            ExportColumn::make("notes")->label(__("dashboard.notes")),
            ExportColumn::make("created_at")->label(__("dashboard.created_at")),
            ExportColumn::make("updated_at")->label(__("dashboard.updated_at")),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body =
            "Your driver export has completed and " .
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
}
