<?php

namespace App\Models;

use App\Enums\Shipping\ShippingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class Shipping extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "client_name",
        "client_email",
        "client_phone",
        "pickup_address",
        "delivery_address",
        "driver_id",
        "tracking_number",
        "status",
        "total_weight",
        "notes",
    ];

    protected $casts = [
        "status" => ShippingStatus::class,
        "total_weight" => "float",
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Shipping $shipping) {
            if (empty($shipping->tracking_number)) {
                $shipping->tracking_number = $shipping->generateTrackingNumber();
            }
        });
    }

    /**
     * Generate a unique tracking number
     *
     * @return string
     */
    protected function generateTrackingNumber(): string
    {
        $prefix = "SHP";
        $year = now()->format("Y");
        $month = now()->format("m");

        // Get the latest shipping record for this month
        $latestShipping = static::where(
            "tracking_number",
            "like",
            "$prefix-$year$month-%"
        )
            ->orderBy("id", "desc")
            ->first();

        $sequence = 1;
        if ($latestShipping) {
            // Extract the sequence number from the latest shipping
            $parts = explode("-", $latestShipping->tracking_number);
            $sequence = intval(end($parts)) + 1;
        }

        return "$prefix-$year$month-" .
            str_pad($sequence, 4, "0", STR_PAD_LEFT);
    }

    /**
     * Recalculate total weight of shipping items
     *
     * @return float
     */
    public function recalculateTotalWeight(): float
    {
        $this->total_weight = $this->items()->sum("total_weight");
        $this->save();

        return $this->total_weight;
    }

    /**
     * Get the items for this shipping
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShippingItem::class);
    }

    /**
     * Get the driver associated with the shipping
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Format the client phone number
     *
     * @return string|null
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->client_phone) {
            return null;
        }

        return PhoneInputNumberType::format($this->client_phone);
    }

    /**
     * Scope a query to only include active/in-progress shippings
     */
    public function scopeActive($query)
    {
        return $query->whereIn("status", [
            ShippingStatus::Pending->value,
            ShippingStatus::Picked_Up->value,
            ShippingStatus::In_Transit->value,
        ]);
    }

    /**
     * Scope a query to only include shippings of a given status
     */
    public function scopeStatus($query, ShippingStatus $status)
    {
        return $query->where("status", $status);
    }
}
