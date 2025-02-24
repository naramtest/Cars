<?php

namespace App\Filament\Resources\VehicleTypeResource\Pages;

use App\Filament\Resources\VehicleTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVehicleTypes extends ManageRecords
{
    use ManageRecords\Concerns\Translatable;

    protected static string $resource = VehicleTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make(), Actions\LocaleSwitcher::make()];
    }
}
