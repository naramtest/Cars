<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Models\Abstract\MoneyModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Money\Money;

class Payment extends MoneyModel
{
    protected $fillable = [
        "amount",
        "currency_code",
        "payment_method",
        "status",
        "payment_link",
        "payment_link_expires_at",
        "provider_id",
        "metadata",
    ];

    protected $casts = [
        "payment_link_expires_at" => "datetime",
        "metadata" => "array",
        "status" => PaymentStatus::class,
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    public function isLinkExpired(): bool
    {
        return $this->payment_link_expires_at &&
            $this->payment_link_expires_at->isPast();
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->currencyService->format($this->amount_money);
    }

    public function getAmountMoneyAttribute(): Money
    {
        return $this->currencyService->money(
            $this->amount,
            $this->currency_code
        );
    }
}
