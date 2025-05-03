<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentProviderInterface
{
    public function getProviderName(): string;

    public function supportsPaymentLinks(): bool;

    public function createPaymentLink(Payment $payment): ?string;

    public function getPaymentFormData(Payment $payment): array;

    public function checkPaymentStatus(Payment $payment): string;

    public function handleWebhook(Request $request): array;
}
