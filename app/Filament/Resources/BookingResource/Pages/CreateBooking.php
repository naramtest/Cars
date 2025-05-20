<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Events\BookingCreated;
use App\Filament\Resources\BookingResource;
use App\Traits\HasPaymentActions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    use HasPaymentActions;

    protected static string $resource = BookingResource::class;

    protected bool $shouldSendPaymentLink = false;

    public function afterCreate(): void
    {
        if (\App::isProduction()) {
            BookingCreated::dispatch($this->record);
        }
        if ($this->shouldSendPaymentLink) {
            $this->handlePaymentLinkGeneration($this->record);
            $this->handleSendingPaymentLink($this->record);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make("create")
                ->label(
                    __(
                        "filament-panels::resources/pages/create-record.form.actions.create.label"
                    )
                )
                ->color("primary")
                ->action(function () {
                    $this->create();
                }),
            Action::make("create and send payment link")
                ->label(__("dashboard.Create & Send PayLink"))
                ->icon("gmdi-credit-card-o")
                ->color("success")
                ->action(function () {
                    $this->shouldSendPaymentLink = true;
                    $this->create();
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [$this->getCancelFormAction()];
    }
}
