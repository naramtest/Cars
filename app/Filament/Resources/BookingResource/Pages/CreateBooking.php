<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Events\BookingCreated;
use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    public function afterCreate(): void
    {
        BookingCreated::dispatch($this->record);
    }
}
