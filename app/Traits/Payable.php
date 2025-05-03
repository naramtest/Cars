<?php namespace App\Traits;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Payable
{
    public function getLastPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    /**
     * Get all payments for this model.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, "payable");
    }

    /**
     * Get the most recent pending payment.
     */
    public function getPendingPayment(): ?Payment
    {
        return $this->payments()->where("status", "pending")->latest()->first();
    }

    public function hasPendingPayment(): bool
    {
        return $this->payments()->where("status", "pending")->exists();
    }

    /**
     * Check if the model has been paid.
     */
    public function isPaid(): bool
    {
        return $this->payments()->where("status", "paid")->exists();
    }

    public function successfulPayments(): Collection
    {
        return $this->payments()->where("status", "paid")->get();
    }

    /**
     * Get total amount paid.
     */
    public function getTotalPaidAmount(): int
    {
        return $this->payments()->where("status", "paid")->sum("amount");
    }
}
