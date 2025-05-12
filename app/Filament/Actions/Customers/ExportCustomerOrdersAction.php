<?php

namespace App\Filament\Actions\Customers;

use App\Exports\CustomerOrdersExport;
use App\Models\Customer;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class ExportCustomerOrdersAction
{
    public static function make()
    {
        return Action::make("exportOrders")
            ->label("Export Orders")
            ->icon("gmdi-download-o")
            ->color("success")
            ->action(function (Customer $customer) {
                // Generate a filename
                $filename =
                    "customer_{$customer->id}_orders_" .
                    now()->format("Y-m-d_H-i-s") .
                    ".xlsx";

                // Export to storage first
                Excel::store(
                    new CustomerOrdersExport($customer),
                    $filename,
                    "public"
                );

                // Return download response
                return response()
                    ->download(Storage::disk("public")->path($filename))
                    ->deleteFileAfterSend();
            })
            ->tooltip(
                "Export all orders (Bookings, Rents, Shippings) to Excel"
            );
    }
}
