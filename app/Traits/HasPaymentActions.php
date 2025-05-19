<?php

namespace App\Traits;

use App\Enums\Payments\PaymentType;
use App\Models\Abstract\Payable;
use App\Services\Payments\PaymentManager;
use App\Services\WhatsApp\Customer\Payment\CPaymentLinkHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Filament\Notifications\Notification;

trait HasPaymentActions
{
    protected function handlePaymentLinkGeneration(Payable $record): void
    {
        $paymentService = app(PaymentManager::class)->driver(
            PaymentType::STRIPE_LINK
        );

        $existingPayment = $record->payment;

        if ($existingPayment and $existingPayment->isPaid()) {
            Notification::make()
                ->title("Payment Already Processed")
                ->body(
                    "This " .
                        class_basename($record) .
                        " has already been paid for. No additional payment is required."
                )
                ->success()
                ->send();

            return;
        }

        if (!$existingPayment) {
            $paymentService->pay($record, $record->total_price);
        }
        $this->handleSendingPaymentLink($record);
    }

    protected function handleSendingPaymentLink(Payable $record): void
    {
        if (!$record->payment || !$record->payment->payment_link) {
            Notification::make()
                ->title("No Payment Link Available")
                ->body("Please generate a payment link first.")
                ->warning()
                ->send();

            return;
        }

        try {
            $whatsAppService = app(WhatsAppNotificationService::class);
            $whatsAppService->sendAndSave(CPaymentLinkHandler::class, $record);

            Notification::make()
                ->title("Payment Link Sent")
                ->body(
                    "The payment link has been sent to the customer via WhatsApp."
                )
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title("Failed to Send Payment Link")
                ->body("Error: " . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function isVisible(Payable $record): bool
    {
        return !$record->isPaid() &&
            in_array($record->status->value, ["pending", "confirmed"]);
    }
}
