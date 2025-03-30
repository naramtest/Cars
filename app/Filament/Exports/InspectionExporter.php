<?php

namespace App\Filament\Exports;

use App\Models\Inspection;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class InspectionExporter extends Exporter
{
    protected static ?string $model = Inspection::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make("id")->label("ID"),
            ExportColumn::make("vehicle.name")->label(__("dashboard.Vehicle")),
            ExportColumn::make("inspection_by")->label(
                __("dashboard.inspection_by")
            ),
            ExportColumn::make("inspection_date")->label(
                __("dashboard.inspection_date")
            ),
            ExportColumn::make("status")
                ->label(__("dashboard.inspection_status"))
                ->formatStateUsing(
                    fn(
                        Inspection $inspection
                    ) => $inspection->status->getLabel()
                ),
            ExportColumn::make("repair_status")
                ->label(__("dashboard.repair_status"))
                ->formatStateUsing(
                    fn(
                        Inspection $inspection
                    ) => $inspection->repair_status->getLabel()
                ),
            ExportColumn::make("incoming_date")->label(
                __("dashboard.incoming_date")
            ),
            ExportColumn::make("meter_reading_km")->label(
                __("dashboard.meter_reading_km")
            ),
            ExportColumn::make("amount")
                ->label(__("dashboard.amount"))
                ->formatStateUsing(
                    fn(Inspection $inspection) => $inspection->formatted_amount
                ),
            ExportColumn::make("notes")->label(__("dashboard.notes")),
            ExportColumn::make("created_at")->label(__("dashboard.created_at")),
            ExportColumn::make("updated_at")->label(__("dashboard.updated_at")),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body =
            "Your inspection export has completed and " .
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
