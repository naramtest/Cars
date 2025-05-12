<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Actions\Customers\ExportCustomerOrdersAction;
use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [ExportCustomerOrdersAction::make(), Actions\EditAction::make()];
    }
}
