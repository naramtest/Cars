<?php

namespace App\Models;

use App\Enums\Booking\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "client_name",
        "client_email",
        "client_phone",
        "start_datetime",
        "end_datetime",
        "vehicle_id",
        "driver_id",
        "address",
        "addons",
        "status",
        "notes",
    ];

    protected $casts = [
        "start_datetime" => "datetime",
        "end_datetime" => "datetime",
        "addons" => "array",
        "status" => BookingStatus::class,
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
    public function getDurationInDaysAttribute(): float
    {
        $start = $this->start_datetime;
        $end = $this->end_datetime;

        return $start->diffInDays($end) +
            ($start->diffInHours($end) % 24 > 0 ? 1 : 0);
    }

    /**
     * Calculate the total price of the booking.
     */
    public function getTotalPriceAttribute(): float
    {
        $dailyRate = $this->vehicle->daily_rate ?? 0;
        $duration = $this->duration_in_days;

        $addonsTotal = 0;
        if ($this->addons && is_array($this->addons)) {
            foreach ($this->addons as $addon) {
                $addonsTotal += $addon["price"] ?? 0;
            }
        }

        return $dailyRate * $duration + $addonsTotal;
    }

    /**
     * Scope a query to only include bookings of a given status.
     */
    public function scopeStatus($query, BookingStatus $status)
    {
        return $query->where("status", $status);
    }

    /**
     * Scope a query to only include active bookings.
     */
    public function scopeActive($query)
    {
        return $query->whereIn("status", [
            BookingStatus::Pending->value,
            BookingStatus::OnGoing->value,
        ]);
    }
}
