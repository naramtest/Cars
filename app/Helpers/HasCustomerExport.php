<?php

namespace App\Helpers;

use App\Models\Shipping;
use Filament\Actions\Exports\ExportColumn;

class HasCustomerExport
{
    public static function get(): array
    {
        return [
            ExportColumn::make("client_name")
                ->label(__("dashboard.client_name"))
                ->formatStateUsing(
                    fn(Shipping $shipping) => $shipping->getCustomer()->name
                ),
            ExportColumn::make("client_email")
                ->label(__("dashboard.client_email"))
                ->formatStateUsing(
                    fn(Shipping $shipping) => $shipping->getCustomer()->email
                ),
            ExportColumn::make("client_phone")
                ->label(__("dashboard.client_phone"))
                ->formatStateUsing(
                    fn(Shipping $shipping) => $shipping->getCustomer()
                        ->phone_number
                ),
        ];
    }
}
