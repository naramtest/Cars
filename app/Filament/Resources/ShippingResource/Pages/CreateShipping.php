<?php

namespace App\Filament\Resources\ShippingResource\Pages;

use App\Filament\Resources\ShippingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShipping extends CreateRecord
{
    protected static string $resource = ShippingResource::class;

    protected function afterCreate(): void
    {
        // Calculate the total weight from the items
        $this->record->recalculateTotalWeight();
    }
}
