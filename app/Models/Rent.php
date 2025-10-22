<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Models\Abstract\Payable;
use App\Traits\CheckStatus;
use App\Traits\HasNotifications;
use App\Traits\HasReferenceNumber;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;

class Rent extends Payable
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
        if (!$this->rental_end_date || !$this->rental_start_date) {
            return 0;
        }

        return $this->rental_start_date
                ->startOfDay()
                ->diffInDays($this->rental_end_date->startOfDay()) + 1;
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->rental_end_date || !$this->rental_start_date) {
            return "0 days";
        }

        // Get exact duration in hours
        $durationInHours = $this->rental_start_date->diffInHours(
            $this->rental_end_date
        );

        // Calculate days and remaining hours
        $days = floor($durationInHours / 24);
        $hours = $durationInHours % 24;

        if ($days == 0) {
            return $hours . " " . ($hours == 1 ? "hour" : "hours");
        } elseif ($hours == 0) {
            return $days . " " . ($days == 1 ? "day" : "days");
        } else {
            return $days .
                " " .
                ($days == 1 ? "day" : "days") .
                " and " .
                $hours .
                " " .
                ($hours == 1 ? "hour" : "hours");
        }
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
            ->limit(1); // Limit to one customer
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    protected function getReferenceNumberPrefix(): string
    {
        return "RNT";
    }
}
