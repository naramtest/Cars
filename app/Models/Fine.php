<?php

namespace App\Models;

use App\Enums\FineStatus;
use App\Models\Abstract\MoneyModel;
use App\Traits\HasReferenceNumber;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Money\Money;

class Fine extends MoneyModel
{
    use HasReferenceNumber;

    protected $fillable = [
        'reference_number',
        'name',
        'rent_id',
        'amount',
        'status',
        'last_notification_sent_at',
    ];

    protected $casts = [
        'status' => FineStatus::class,
        'last_notification_sent_at' => 'datetime',
    ];


    public function rent(): BelongsTo
    {
        return $this->belongsTo(Rent::class);
    }


    public function vehicle()
    {
        return $this->hasOneThrough(Vehicle::class, Rent::class, 'id', 'id', 'rent_id', 'vehicle_id');
    }


    public function getCustomer()
    {
        return $this->rent->getCustomer();
    }


    public function getAmountMoneyAttribute(): Money
    {
        return $this->currencyService->money($this->amount, 'AED');
    }


    public function getFormattedAmountAttribute(): string
    {
        return $this->currencyService->format($this->amount_money);
    }


    public function updateLastNotificationSent(): self
    {
        $this->update(['last_notification_sent_at' => now()]);
        return $this;
    }


    public function scopePending($query)
    {
        return $query->where('status', FineStatus::Pending);
    }


    public function scopeNeedsNotification($query)
    {
        return $query->where('status', FineStatus::Pending)
            ->where(function ($q) {
                $q->whereNull('last_notification_sent_at')
                    ->orWhere('last_notification_sent_at', '<=', now()->subHours(24));
            });
    }
}
