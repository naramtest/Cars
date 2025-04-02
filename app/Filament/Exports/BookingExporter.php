<?php

namespace App\Filament\Exports;

use App\Models\Booking;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BookingExporter extends Exporter
{
    protected static ?string $model = Booking::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make("id")->label("ID"),
            ExportColumn::make("client_name")->label(
                __("dashboard.client_name")
            ),
            ExportColumn::make("client_email")->label(
                __("dashboard.client_email")
            ),
            ExportColumn::make("client_phone")->label(
                __("dashboard.client_phone")
            ),
            ExportColumn::make("start_datetime")
                ->label(__("dashboard.start_datetime"))
                ->formatStateUsing(
                    fn(Booking $booking) => $booking->start_datetime->format(
                        "Y-m-d H:i"
                    )
                ),
            ExportColumn::make("end_datetime")
                ->label(__("dashboard.end_datetime"))
                ->formatStateUsing(
                    fn(Booking $booking) => $booking->end_datetime->format(
                        "Y-m-d H:i"
                    )
                ),
            ExportColumn::make("vehicle.name")->label(__("dashboard.Vehicle")),
            ExportColumn::make("vehicle.model")->label(__("dashboard.model")),
            ExportColumn::make("vehicle.license_plate")->label(
                __("dashboard.license_plate")
            ),
            ExportColumn::make("driver.full_name")->label(
                __("dashboard.Driver")
            ),
            ExportColumn::make("pickup_address")->label(
                __("dashboard.pickup_address")
            ),
            ExportColumn::make("destination_address")->label(
                __("dashboard.destination_address")
            ),
            ExportColumn::make("status")
                ->label(__("dashboard.status"))
                ->formatStateUsing(
                    fn(Booking $booking) => $booking->status->value
                ),
            ExportColumn::make("duration_in_days")
                ->label(__("dashboard.duration"))
                ->formatStateUsing(
                    fn(Booking $booking) => $booking->duration_in_days .
                        " " .
                        __("dashboard.days")
                ),
            ExportColumn::make("total_price")
                ->label(__("dashboard.total_price"))
                ->formatStateUsing(
                    fn(Booking $booking) => $booking->formatted_total_price
                ),
            ExportColumn::make("addons")
                ->label(__("dashboard.addons"))
                ->formatStateUsing(
                    fn(Booking $booking) => $booking->addons
                        ->pluck("name")
                        ->implode(", ")
                ),
            ExportColumn::make("notes")->label(__("dashboard.notes")),
            ExportColumn::make("created_at")
                ->label(__("dashboard.created_at"))
                ->formatStateUsing(
                    fn(Booking $booking) => $booking->created_at->format(
                        "Y-m-d H:i"
                    )
                ),
            ExportColumn::make("updated_at")
                ->label(__("dashboard.updated_at"))
                ->formatStateUsing(
                    fn(Booking $booking) => $booking->updated_at->format(
                        "Y-m-d H:i"
                    )
                ),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body =
            "Your booking export has completed and " .
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
