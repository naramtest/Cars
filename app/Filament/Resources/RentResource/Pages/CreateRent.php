<?php

namespace App\Filament\Resources\RentResource\Pages;

use App\Events\RentCreated;
use App\Filament\Resources\RentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRent extends CreateRecord
{
    protected static string $resource = RentResource::class;

    public function afterCreate(): void
    {
        RentCreated::dispatch($this->record);
    }
}
