<?php

namespace App\Models;

use App\Enums\Shipping\ShippingStatus;
use App\Traits\CheckStatus;
use App\Traits\HasNotifications;
use App\Traits\HasReferenceNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipping extends Model
{
    use SoftDeletes;
    use HasReferenceNumber;
    use HasNotifications;

    use CheckStatus;

    protected $fillable = [
        "pickup_address",
        "delivery_address",
        "driver_id",
        "reference_number",
        "status",
        "total_weight",
        "received_at",
        "delivered_at",
        "notes",
        "delivery_notes",
        "pick_up_at",
    ];
    protected $casts = [
        "status" => ShippingStatus::class,
        "total_weight" => "float",
        "received_at" => "datetime",
        "delivered_at" => "datetime",
        "pick_up_at" => "datetime",
    ];

    /**
     * Recalculate total weight of shipping items
     *
     * @return float
     */
    public function recalculateTotalWeight(): float
    {
        $totalWeight =
            $this->items()
                ->selectRaw("SUM(weight * quantity) as calculated_weight")
                ->first()->calculated_weight ?? 0;

        $this->total_weight = floatval($totalWeight);
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
     * Scope a query to only include active/in-progress shippings
     */
    public function scopeActive($query)
    {
        return $query->whereIn("status", [
            ShippingStatus::Pending->value,
            ShippingStatus::Picked_Up->value,
        ]);
    }

    /**
     * Scope a query to only include shippings of a given status
     */
    public function scopeStatus($query, ShippingStatus $status)
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
        return "SHP";
    }
}
