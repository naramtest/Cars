<?php

namespace App\Models\Abstract;

use App\Enums\Payments\PaymentStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Payable extends MoneyModel
{
    /**
     * Get the latest payment for this model
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, "payable")->latestOfMany();
    }

    /**
     * Get the first successful payment
     */
    public function paidPayment(): MorphOne
    {
        return $this->morphOne(Payment::class, "payable")
            ->where("status", PaymentStatus::PAID)
            ->latestOfMany();
    }

    /**
     * Check if the model has been paid.
     */
    public function isPaid(): bool
    {
        return $this->payments()
            ->where("status", PaymentStatus::PAID)
            ->exists();
    }

    /**
     * Get all payments for this model
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, "payable");
    }

    /**
     * Update an existing payment or create a new one if no ID is provided
     */
    public function updatePayment(
        array $attributes,
        ?int $paymentId = null
    ): Payment {
        if ($paymentId) {
            $payment = $this->payments()->findOrFail($paymentId);
            $payment->update($attributes);
            return $payment->refresh();
        }

        return $this->createPayment($attributes);
    }

    /**
     * Create a new payment for this payable model
     *
     * @param array $attributes
     * @return Payment
     */
    public function createPayment(array $attributes): Payment
    {
        return $this->payments()->create($attributes);
    }

    public function getPaymentStatusArray(): array
    {
        $totalPaid = $this->getTotalPaidAmount();
        $totalPrice = $this->total_price ?? 0;

        return [
            "total_price" => $totalPrice,
            "total_paid" => $totalPaid,
            "is_fully_paid" => $totalPaid >= $totalPrice,
            "formatted_total" => $this->getFormattedTotalPriceAttribute(),
            "formatted_paid" => $this->currencyService->format(
                $this->currencyService->money($totalPaid)
            ),

            "payment_percentage" =>
                $totalPrice > 0
                    ? min(100, round(($totalPaid / $totalPrice) * 100))
                    : 0,
        ];
    }

    /**
     * Get the total amount paid for this model
     */
    public function getTotalPaidAmount(): int
    {
        return $this->payments()
            ->where("status", PaymentStatus::PAID)
            ->sum("amount");
    }
}
