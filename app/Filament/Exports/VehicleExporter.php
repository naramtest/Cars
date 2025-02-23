<?php

namespace App\Filament\Exports;

use App\Models\Vehicle;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class VehicleExporter extends Exporter
{
    public static function getColumns(): array
    {
        return [
            ExportColumn::make("id")->label("ID"),
            ExportColumn::make("name")->label(__("dashboard.name")),
            ExportColumn::make("model")->label(__("dashboard.model")),
            ExportColumn::make("engine_number")->label(
                __("dashboard.engine_number")
            ),
            ExportColumn::make("engine_type")->label(
                __("dashboard.engine_type")
            ),
            ExportColumn::make("license_plate")->label(
                __("dashboard.license_plate")
            ),
            ExportColumn::make("registration_expiry_date")->label(
                __("dashboard.registration_expiry_date")
            ),
            ExportColumn::make("daily_rate")->label(__("dashboard.daily_rate")),
            ExportColumn::make("year_of_first_immatriculation")->label(
                __("dashboard.year_of_first_immatriculation")
            ),
            ExportColumn::make("gearbox")
                ->label(__("dashboard.gearbox"))
                ->formatStateUsing(
                    fn(Vehicle $vehicle) => $vehicle->gearbox->value
                ),
            ExportColumn::make("fuel_type")
                ->label(__("dashboard.fuel_type"))
                ->formatStateUsing(
                    fn(Vehicle $vehicle) => $vehicle->fuel_type->value
                ),
            ExportColumn::make("number_of_seats")->label(
                __("dashboard.number_of_seats")
            ),
            ExportColumn::make("kilometer")->label(__("dashboard.kilometer")),
            ExportColumn::make("options")
                ->label(__("dashboard.options"))
                ->formatStateUsing(
                    fn(Vehicle $vehicle) => implode(
                        ", ",
                        $vehicle->options ?? []
                    )
                ),
            ExportColumn::make("notes")->label(__("dashboard.notes")),
            ExportColumn::make("created_at")->label(__("dashboard.created_at")),
            ExportColumn::make("updated_at")->label(__("dashboard.updated_at")),
            ExportColumn::make("types")
                ->label(__("dashboard.types"))
                ->formatStateUsing(
                    fn(Vehicle $vehicle) => $vehicle->types
                        ->pluck("name")
                        ->implode(", ")
                ),
            ExportColumn::make("driver.full_name")->label(
                __("dashboard.Driver")
            ),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body =
            "Your vehicle export has completed and " .
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
