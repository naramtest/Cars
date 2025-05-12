<?php

namespace App\Filament\Resources\RentResource\Pages;

use App\Enums\ReservationStatus;
use App\Filament\Resources\RentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRents extends ListRecords
{
    protected static string $resource = RentResource::class;

    public function getTabs(): array
    {
        return ReservationStatus::tabs();
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
