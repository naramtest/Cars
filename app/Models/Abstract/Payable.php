<?php

namespace App\Models\Abstract;

use App\Enums\Payments\PaymentStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Payable extends MoneyModel
{
    public function updatePayment(array $attributes): Payment
    {
        if ($this->payment) {
            $this->payment->update($attributes);
            return $this->payment->refresh();
        }

        return $this->payment()->create($attributes);
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, "payable");
    }

    /**
     * Check if the model has been paid.
     */
    public function isPaid(): bool
    {
        return $this->payment && $this->payment->status === PaymentStatus::PAID;
    }
}
