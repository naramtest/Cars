<?php

namespace App\Models;

use App\Enums\Booking\BookingStatus;
use App\Services\Currency\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;

class Booking extends Model
{
    use SoftDeletes;

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
     * Calculate the total price of the booking as a Money object.
     *
     * @return Money
     */
    public function getTotalPriceMoneyAttribute(): Money
    {
        //TODO: add to column and from

        // If there's no vehicle, return zero
        if (!$this->vehicle) {
            return $this->currencyService()->money(0);
        }

        $dailyRateMoney = $this->vehicle->daily_rate_money;
        $duration = $this->duration_in_days;

        // Start with the daily rate multiplied by the duration
        $totalPrice = $dailyRateMoney->multiply($duration);

        // Add addon prices
        if ($this->addons && is_array($this->addons)) {
            foreach ($this->addons as $addon) {
                //                TODO: convert to money
                $addonPrice = $addon["price"] ?? 0;

                if ($addonPrice > 0) {
                    // Convert addon price to Money object in the same currency
                    $addonMoney = $this->currencyService()->parse(
                        $addonPrice,
                        $this->currencyService()->getDefaultCurrency()
                    );

                    // Add to total
                    $totalPrice = $totalPrice->add($addonMoney);
                }
            }
        }

        return $totalPrice;
    }

    /**
     * Get the currency service instance
     *
     * @return CurrencyService
     */
    protected function currencyService(): CurrencyService
    {
        return app(CurrencyService::class);
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
        return $this->currencyService()->format(
            $this->total_price_money,
            app()->getLocale()
        );
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

    /**
     * Get the addons attached to this booking.
     */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, "booking_addon")
            ->withPivot("quantity")
            ->withTimestamps();
    }
}
