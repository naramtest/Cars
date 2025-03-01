<?php

namespace App\Filament\Exports;

use App\Models\Shipping;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ShippingExporter extends Exporter
{
    protected static ?string $model = Shipping::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make("id")->label("ID"),
            ExportColumn::make("tracking_number")->label(
                __("dashboard.tracking_number")
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
            ExportColumn::make("driver.full_name")->label(
                __("dashboard.Driver")
            ),
            ExportColumn::make("status")
                ->label(__("dashboard.status"))
                ->formatStateUsing(
                    fn(Shipping $shipping) => $shipping->status->value
                ),
            ExportColumn::make("total_weight")
                ->label(__("dashboard.total_weight"))
                ->formatStateUsing(
                    fn(Shipping $shipping) => $shipping->total_weight . " kg"
                ),
            ExportColumn::make("items_count")
                ->label(__("dashboard.items"))
                ->formatStateUsing(
                    fn(Shipping $shipping) => $shipping->items()->count()
                ),
            ExportColumn::make("pickup_address")->label(
                __("dashboard.pickup_address")
            ),
            ExportColumn::make("delivery_address")->label(
                __("dashboard.delivery_address")
            ),
            ExportColumn::make("notes")->label(__("dashboard.notes")),
            ExportColumn::make("created_at")->label(__("dashboard.created_at")),
            ExportColumn::make("updated_at")->label(__("dashboard.updated_at")),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body =
            "Your shipping export has completed and " .
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
