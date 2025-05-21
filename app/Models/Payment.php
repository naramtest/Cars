<?php

namespace App\Models;

use App\Enums\Payments\PaymentStatus;
use App\Enums\Payments\PaymentType;
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
        "note",
        "paid_at",
    ];

    protected $casts = [
        "payment_link_expires_at" => "datetime",
        "metadata" => "array",
        "status" => PaymentStatus::class,
        "paid_at" => "datetime",
        "payment_method" => PaymentType::class,
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

    public function updatePaymentStatus(
        PaymentStatus $newStatus,
        array $metadataUpdates = [],
        $paid_at = null
    ): array {
        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->paid_at = $paid_at;

        // Update metadata
        $this->metadata = array_merge($this->metadata ?? [], $metadataUpdates);
        $this->save();
        return [
            "status" => "success",
            "payment_id" => $this->id,
            "old_status" => $oldStatus->value,
            "new_status" => $newStatus->value,
        ];
    }

    public function updatePaymentToPaid(
        array $metadataUpdates = [],
        $paid_at = null
    ): array {
        $oldStatus = $this->status;
        $this->status = PaymentStatus::PAID;
        $this->paid_at = $paid_at ?? now()->toIso8601String();

        // Update metadata
        $this->metadata = array_merge($this->metadata ?? [], $metadataUpdates);
        $this->save();
        return [
            "status" => "success",
            "payment_id" => $this->id,
            "old_status" => $oldStatus->value,
            "new_status" => PaymentStatus::PAID,
        ];
    }

    public function scopePaid($query)
    {
        return $query->where("status", PaymentStatus::PAID);
    }

    public function scopePending($query)
    {
        return $query->where("status", PaymentStatus::PENDING);
    }
}
