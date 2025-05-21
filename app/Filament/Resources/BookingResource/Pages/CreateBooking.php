<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Events\BookingCreated;
use App\Filament\Resources\BookingResource;
use App\Traits\HasPaymentActions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\HtmlString;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class CreateBooking extends CreateRecord
{
    use HasPaymentActions;

    protected static string $resource = BookingResource::class;

    protected bool $shouldSendPaymentLink = false;
    protected string $amount = "";
    protected ?string $note = "";

    public function afterCreate(): void
    {
        if (\App::isProduction()) {
            BookingCreated::dispatch($this->record);
        }

        if ($this->shouldSendPaymentLink) {
            $this->generateAndSend($this->record, [
                "amount" => $this->amount,
                "note" => $this->note,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return $this->actions();
    }

    /**
     * @return array
     */
    public function actions(): array
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
                ->modalWidth("lg")
                ->form([
                    MoneyInput::make("amount")
                        ->required()
                        ->label(__("dashboard.amount"))
                        ->default(function () {
                            //TODO: add function to convert to subunit instead of * 100
                            return $this->data["total_price"] * 100 ?? 0;
                        })
                        ->helperText(function () {
                            return new HtmlString(
                                '<span class="text-danger-600 dark:text-danger-400">' .
                                    __("dashboard.amount_payment_note") .
                                    "</span>"
                            );
                        }),
                    Textarea::make("note")
                        ->label("Payment Note")
                        ->rows(2)
                        ->placeholder(
                            "Enter a note about this payment (optional)"
                        )
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $this->shouldSendPaymentLink = true;
                    $this->amount = $data["amount"];
                    $this->note = $data["note"];
                    $this->create();
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->actions();
    }
}
