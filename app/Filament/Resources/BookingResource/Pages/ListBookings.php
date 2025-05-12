<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Enums\Checkout\OrderStatus;
use App\Enums\ReservationStatus;
use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    public function getTabs(): array
    {
        return ReservationStatus::tabs();
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
