<?php

namespace App\Services\Payments;

use App\Enums\Payments\PaymentStatus;
use App\Models\Abstract\Payable;
use App\Models\Payment;

class PaymentService
{
    public function __construct(protected PaymentProviderInterface $provider) {}

    public function generateAndPay(
        Payable $payable,
        int $amount,
        ?string $currency = null,
        ?string $note = null
    ): Payment {
        $currency ??= config("app.money_currency");

        // Create new payment
        $payment = $payable->createPayment([
            "amount" => $amount,
            "currency_code" => $currency,
            "payment_method" => $this->provider->getProviderName(),
            "status" => PaymentStatus::PENDING,
            "note" => $note,
        ]);

        // Process payment with provider
        $payment = $this->provider->pay($payment);
        $payment->save();

        // Create initial payment attempt record
        $payment->attempts()->create([
            "status" => PaymentStatus::PENDING,
            "provider_data" => [
                "source" => "creation",
                "provider" => $this->provider->getProviderName(),
            ],
        ]);

        return $payment;
    }

    public function pay(Payment $payment): Payment
    {
        // Process payment with provider
        $payment = $this->provider->pay($payment);
        $payment->save();

        // Create initial payment attempt record
        $payment->attempts()->create([
            "status" => PaymentStatus::PENDING,
            "provider_data" => [
                "source" => "creation",
                "provider" => $this->provider->getProviderName(),
            ],
        ]);

        return $payment;
    }
}
