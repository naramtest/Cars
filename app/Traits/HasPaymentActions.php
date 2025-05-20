<?php

namespace App\Traits;

use App\Enums\Payments\PaymentType;
use App\Models\Abstract\Payable;
use App\Services\Payments\PaymentManager;
use App\Services\WhatsApp\Customer\Payment\CPaymentLinkHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Exception;
use Filament\Notifications\Notification;

trait HasPaymentActions
{
    protected function handlePaymentLinkGeneration(Payable $record): bool
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

            return false;
        }

        //TODO: if total price changes and payment is not paid update payment and payment link
        if (!$existingPayment) {
            $paymentService->pay($record, $record->total_price);
        }
        $record->refresh();

        return true;
    }

    protected function handleSendingPaymentLink(Payable $record): bool
    {
        if ($record->payment and !$record->payment->payment_link) {
            Notification::make()
                ->title("No Payment Link Available")
                ->body("Please generate a payment link first.")
                ->warning()
                ->send();

            return false;
        }

        try {
            $whatsAppService = app(WhatsAppNotificationService::class);
            $whatsAppService->sendAndSave(
                CPaymentLinkHandler::class,
                $record,
                isUpdate: true
            );

            Notification::make()
                ->title("Payment Link Sent")
                ->body(
                    "The payment link has been sent to the customer via WhatsApp."
                )
                ->success()
                ->send();
            return true;
        } catch (Exception $e) {
            logger($e);
            Notification::make()
                ->title("Failed to Send Payment Link")
                ->body("Error: " . $e->getMessage())
                ->danger()
                ->send();
            return false;
        }
    }

    protected function isVisible(Payable $record): bool
    {
        return !$record->isPaid() &&
            in_array($record->status->value, ["pending", "confirmed"]);
    }
}
