<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Models\Abstract\MoneyModel;
use App\Traits\CheckStatus;
use App\Traits\HasNotifications;
use App\Traits\HasReferenceNumber;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;

class Rent extends MoneyModel
{
    use SoftDeletes;
    use HasReferenceNumber;
    use HasNotifications;

    use CheckStatus;

    protected $fillable = [
        "reference_number",
        "rental_start_date",
        "rental_end_date",
        "pickup_address",
        "drop_off_address",
        "status",
        "terms_conditions",
        "description",
        "vehicle_id",
        "total_price",
    ];

    protected $casts = [
        "rental_start_date" => "datetime",
        "rental_end_date" => "datetime",
        "status" => ReservationStatus::class,
    ];

    /**
     * Get the vehicle that owns the rent.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Calculate the total duration of the booking in days.
     */
    public function getDurationInDaysAttribute(): int
    {
        if (!$this->end_datetime || !$this->start_datetime) {
            return 0;
        }

        return $this->start_datetime
            ->startOfDay()
            ->diffInDays($this->end_datetime->startOfDay()) + 1;
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return $this->currencyService->format(
            $this->getTotalPriceMoneyAttribute(),
            app()->getLocale()
        );
    }

    public function getTotalPriceMoneyAttribute(): Money
    {
        return $this->currencyService->money($this->total_price);
    }

    /**
     * Scope a query to only include rents of a given status.
     */
    public function scopeStatus($query, ReservationStatus $status)
    {
        return $query->where("status", $status);
    }

    public function getCustomer()
    {
        return $this->customer()->first();
    }

    // Helper method to get the single customer

    public function customer()
    {
        return $this->morphToMany(Customer::class, "customerable")
            ->withTimestamps()
            ->orderByPivot("created_at", "desc")
            ->limit(1); // Limit to one customer
    }

    protected function getReferenceNumberPrefix(): string
    {
        return "RNT";
    }
}
