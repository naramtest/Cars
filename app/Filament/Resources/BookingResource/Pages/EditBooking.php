<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Traits\HasPaymentActions;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    use HasPaymentActions;

    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
            //            Actions\Action::make("generatePaymentLink")
            //                ->label("Generate Payment Link")
            //                ->icon("heroicon-o-credit-card")
            //                ->action(function (Booking $booking) {
            //                    $this->handlePaymentLinkGeneration($booking);
            //                })
            //                ->visible(function (Booking $booking) {
            //                    return $this->isVisible($booking);
            //                }),
        ];
    }
}
