<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Models\Abstract\Payable;
use App\Traits\CheckStatus;
use App\Traits\HasNotifications;
use App\Traits\HasReferenceNumber;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;

class Booking extends Payable
{
    use SoftDeletes;
    use HasReferenceNumber;
    use HasNotifications;
    use CheckStatus;

    protected $fillable = [
        "start_datetime",
        "end_datetime",
        "vehicle_id",
        "driver_id",
        "pickup_address", // Renamed from address
        "destination_address", // New address column
        "status",
        "notes",
        "reference_number",
        "total_price",
    ];

    protected $casts = [
        "start_datetime" => "datetime",
        "end_datetime" => "datetime",
        "status" => ReservationStatus::class,
    ];

    /**
     * Get the vehicle that owns the booking.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver that owns the booking.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
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

    /**
     * Scope a query to only include bookings of a given status.
     */
    public function scopeStatus($query, ReservationStatus $status)
    {
        return $query->where("status", $status);
    }

    /**
     * Get the addons attached to this booking.
     */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, "booking_addon")
            ->withPivot("quantity")
            ->withTimestamps();
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

    protected function getReferenceNumberPrefix(): string
    {
        return "BOK";
    }
}
