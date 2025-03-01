<?php

namespace App\Models;

use App\Enums\Rent\RentStatus;
use App\Models\Abstract\MoneyModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;

class Rent extends MoneyModel
{
    use SoftDeletes;

    protected $fillable = [
        "rent_number",
        "client_name",
        "client_email",
        "client_phone",
        "rental_start_date",
        "rental_end_date",
        "pickup_address",
        "drop_off_address",
        "status",
        "terms_conditions",
        "description",
        "vehicle_id",
    ];

    protected $casts = [
        "rental_start_date" => "datetime",
        "rental_end_date" => "datetime",
        "status" => RentStatus::class,
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Rent $rent) {
            if (empty($rent->rent_number)) {
                $year = now()->format("Y");
                $month = now()->format("m");

                // Get the next sequential number for this month
                $latestRent = static::where(
                    "rent_number",
                    "like",
                    "R-{$year}{$month}-%"
                )
                    ->orderBy("id", "desc")
                    ->first();

                $sequence = 1;
                if ($latestRent) {
                    // Extract the sequence number from the latest rent
                    $parts = explode("-", $latestRent->rent_number);
                    $sequence = intval(end($parts)) + 1;
                }

                $rent->rent_number =
                    "R-{$year}{$month}-" .
                    str_pad($sequence, 4, "0", STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the vehicle that owns the rent.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    //TODO: extract to a class (and check to see if it should be in hours or days )

    /**
     * Calculate the total duration of the rental in days.
     */
    public function getDurationInDaysAttribute(): float
    {
        if (!$this->rental_end_date) {
            return 0;
        }

        $start = $this->rental_start_date;
        $end = $this->rental_end_date;

        return $start->diffInDays($end) +
            ($start->diffInHours($end) % 24 > 0 ? 1 : 0);
    }

    /**
     * Calculate the total price of the rental as a Money object.
     *
     * @return Money
     */
    public function getTotalPriceMoneyAttribute(): Money
    {
        // If there's no vehicle or end date, return zero
        if (!$this->vehicle || !$this->rental_end_date) {
            return $this->currencyService->money(0);
        }

        $dailyRateMoney = $this->vehicle->daily_rate_money;
        $duration = $this->duration_in_days;

        // Daily rate multiplied by the duration
        return $dailyRateMoney->multiply($duration);
    }

    /**
     * Get the total price as an integer (for storage or calculations)
     *
     * @return int
     */
    public function getTotalPriceAttribute(): int
    {
        return (int) $this->total_price_money->getAmount();
    }

    /**
     * Get the formatted total price.
     *
     * @return string
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return $this->currencyService->format(
            $this->total_price_money,
            app()->getLocale()
        );
    }

    /**
     * Scope a query to only include rents of a given status.
     */
    public function scopeStatus($query, RentStatus $status)
    {
        return $query->where("status", $status);
    }

    /**
     * Scope a query to only include active rents.
     */
    public function scopeActive($query)
    {
        return $query->whereIn("status", [
            RentStatus::Active->value,
            RentStatus::Confirmed->value,
        ]);
    }

    /**
     * Scope a query to only include pending rents.
     */
    public function scopePending($query)
    {
        return $query->whereIn("status", [
            RentStatus::Pending->value,
            RentStatus::Draft->value,
        ]);
    }
}
