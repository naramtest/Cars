<?php

namespace App\Filament\Exports;

use App\Models\Rent;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class RentExporter extends Exporter
{
    protected static ?string $model = Rent::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make("id")->label("ID"),
            ExportColumn::make("rent_number")->label(
                __("dashboard.rent_number")
            ),
            ExportColumn::make("client_name")->label(
                __("dashboard.client_name")
            ),
            ExportColumn::make("client_email")->label(
                __("dashboard.client_email")
            ),
            ExportColumn::make("client_phone")->label(
                __("dashboard.client_phone")
            ),
            ExportColumn::make("vehicle.name")->label(__("dashboard.Vehicle")),
            ExportColumn::make("rental_start_date")->label(
                __("dashboard.start_datetime")
            ),
            ExportColumn::make("rental_end_date")->label(
                __("dashboard.end_datetime")
            ),
            ExportColumn::make("duration_in_days")
                ->label(__("dashboard.duration"))
                ->formatStateUsing(
                    fn(Rent $rent) => $rent->duration_in_days .
                        " " .
                        __("dashboard.days")
                ),
            ExportColumn::make("status")
                ->label(__("dashboard.status"))
                ->formatStateUsing(fn(Rent $rent) => $rent->status->value),
            ExportColumn::make("total_price")
                ->label(__("dashboard.total_price"))
                ->formatStateUsing(
                    fn(Rent $rent) => $rent->formatted_total_price
                ),
            ExportColumn::make("pickup_address")->label(
                __("dashboard.pickup_address")
            ),
            ExportColumn::make("drop_off_address")->label(
                __("dashboard.drop_off_address")
            ),
            ExportColumn::make("description")->label(
                __("dashboard.description")
            ),
            ExportColumn::make("created_at")->label(__("dashboard.created_at")),
            ExportColumn::make("updated_at")->label(__("dashboard.updated_at")),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body =
            "Your rent export has completed and " .
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
