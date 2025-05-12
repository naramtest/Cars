<?php

namespace App\Filament\Resources\ShippingResource\Pages;

use App\Enums\Shipping\ShippingStatus;
use App\Filament\Resources\ShippingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShippings extends ListRecords
{
    protected static string $resource = ShippingResource::class;

    public function getTabs(): array
    {
        return ShippingStatus::tabs();
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
