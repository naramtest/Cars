<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Services\Payments\PaymentService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\Action::make("generatePaymentLink")
                ->label("Generate Payment Link")
                ->icon("heroicon-o-credit-card")
                ->action(function (Booking $booking) {
                    $paymentService = app(PaymentService::class);
                    $existingPayment = $booking
                        ->payments()
                        ->where("status", "pending")
                        ->latest()
                        ->first();

                    if ($existingPayment && $existingPayment->payment_link) {
                        Notification::make()
                            ->title("Payment Link Already Exists")
                            ->body(
                                "A payment link has already been generated for this booking."
                            )
                            ->actions([
                                Action::make("viewLink")
                                    ->label("View Link")
                                    ->url($existingPayment->payment_link, true)
                                    ->button(),
                                Action::make("sendWhatsApp")
                                    ->label("Send WhatsApp")
                                    ->action(function () use (
                                        $booking,
                                        $existingPayment
                                    ) {
                                        $this->sendPaymentLinkWhatsApp(
                                            $booking,
                                            $existingPayment
                                        );
                                    })
                                    ->button(),
                            ])
                            ->info()
                            ->send();

                        return;
                    }

                    // Generate new payment link
                    $amount = $booking->total_price;
                    $currency = config("app.money_currency", "USD");

                    $payment = $paymentService->generatePaymentLink(
                        $booking,
                        $amount,
                        $currency
                    );

                    Notification::make()
                        ->title("Payment Link Generated")
                        ->body(
                            "A payment link has been generated successfully."
                        )
                        ->actions([
                            Action::make("viewLink")
                                ->label("View Link")
                                ->url($payment->payment_link, true)
                                ->button(),
                            Action::make("sendWhatsApp")
                                ->label("Send WhatsApp")
                                ->action(function () use ($booking, $payment) {
                                    $this->sendPaymentLinkWhatsApp(
                                        $booking,
                                        $payment
                                    );
                                })
                                ->button(),
                        ])
                        ->success()
                        ->send();
                })
                ->visible(
                    fn(Booking $booking) => in_array($booking->status->value, [
                        "pending",
                        "confirmed",
                    ])
                ),
        ];
    }
}
