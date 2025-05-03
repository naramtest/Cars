<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(protected PaymentProviderInterface $provider) {}

    public function generatePayment(
        Model $model,
        int $amount,
        string $currency
    ): Payment {
        // Create payment record - common for both approaches
        $payment = new Payment([
            "amount" => $amount,
            "currency_code" => $currency,
            "payment_method" => $this->provider->getProviderName(),
            "status" => PaymentStatus::PENDING->value,
        ]);

        $model->payments()->save($payment);

        // Generate payment link if provider supports it
        if ($this->provider->supportsPaymentLinks()) {
            $paymentLink = $this->provider->createPaymentLink($payment);

            if ($paymentLink) {
                $payment->payment_link = $paymentLink;
                $payment->payment_link_expires_at = Carbon::now()->addDays(
                    config(
                        "payment.providers." .
                            $this->provider->getProviderName() .
                            ".link_expiration_days",
                        7
                    )
                );
            }
        } else {
            // For direct payment methods, store form data
            $payment->metadata = array_merge($payment->metadata ?? [], [
                "payment_form_data" => $this->provider->getPaymentFormData(
                    $payment
                ),
            ]);
        }

        $payment->save();

        // Create initial payment attempt record
        $payment->attempts()->create([
            "status" => PaymentStatus::PENDING->value,
            "provider_data" => [
                "source" => "creation",
                "provider" => $this->provider->getProviderName(),
            ],
        ]);

        return $payment;
    }

    public function checkStatus(Payment $payment): string
    {
        // If the payment link has expired, mark as failed
        if ($payment->isLinkExpired() && $payment->isPending()) {
            $oldStatus = $payment->status;
            $payment->status = PaymentStatus::FAILED;
            $payment->save();

            Log::info("Payment status changed due to link expiration", [
                "payment_id" => $payment->id,
                "old_status" => $oldStatus->value,
                "new_status" => $payment->status->value,
            ]);

            return $payment->status->value;
        }

        // Check with the provider
        $status = $this->provider->checkPaymentStatus($payment);

        // Update if the status has changed
        if ($payment->status->value !== $status) {
            $oldStatus = $payment->status;
            $payment->status = $status;
            $payment->save();

            Log::info("Payment status changed from provider check", [
                "payment_id" => $payment->id,
                "old_status" => $oldStatus->value,
                "new_status" => $status,
                "provider" => $this->provider->getProviderName(),
            ]);
        }

        return $status;
    }

    public function handleWebhook(Request $request): array
    {
        return $this->provider->handleWebhook($request);
    }
}
