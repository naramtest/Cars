<?php

namespace App\Services\Payments;

use App\Enums\Payments\PaymentStatus;
use App\Models\Abstract\Payable;
use App\Models\Payment;

class PaymentService
{
    public function __construct(protected PaymentProviderInterface $provider) {}

    public function linkPayment(
        Payable $payable,
        int $amount,
        ?string $currency
    ): string {
        $payment = $this->pay($payable, $amount, $currency);
        return $payment->payment_link;
    }

    public function pay(
        Payable $payable,
        int $amount,
        ?string $currency
    ): Payment {
        $currency ??= config("app.money_currency");
        //1- create payment object
        $payment = $payable->updatePayment([
            "amount" => $amount,
            "currency_code" => $currency,
            "payment_method" => $this->provider->getProviderName(),
            "status" => PaymentStatus::PENDING,
        ]);

        //2- pay
        $payment = $this->provider->pay($payment);

        //3- save after pay
        $payment->save();

        //4-  Create initial payment attempt record
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
