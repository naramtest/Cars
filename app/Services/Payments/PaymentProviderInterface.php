<?php

namespace App\Services\Payments;

use App\Enums\Payments\PaymentType;
use App\Models\Payment;

interface PaymentProviderInterface
{
    public function getProviderName(): PaymentType;

    public function pay(Payment $payment): Payment;
}
