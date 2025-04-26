<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make("id")->label("ID"),
            ExportColumn::make("name")->label(__("dashboard.name")),
            ExportColumn::make("email")->label(__("dashboard.email")),
            ExportColumn::make("phone_number")->label(
                __("dashboard.phone_number")
            ),
            ExportColumn::make("notes")->label(__("dashboard.notes")),

            // Add relationships counts
            ExportColumn::make("bookings_count")
                ->label(__("dashboard.Bookings"))
                ->state(
                    fn(Customer $customer) => $customer->bookings()->count()
                ),

            ExportColumn::make("rents_count")
                ->label(__("dashboard.Rents"))
                ->state(fn(Customer $customer) => $customer->rents()->count()),

            ExportColumn::make("shippings_count")
                ->label(__("dashboard.Shippings"))
                ->state(
                    fn(Customer $customer) => $customer->shippings()->count()
                ),

            ExportColumn::make("created_at")->label(__("dashboard.created_at")),
            ExportColumn::make("updated_at")->label(__("dashboard.updated_at")),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body =
            "Your customer export has completed and " .
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
