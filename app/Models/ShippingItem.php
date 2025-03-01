<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingItem extends Model
{
    protected $fillable = [
        "shipping_id",
        "name",
        "quantity",
        "weight",
        "description",
    ];

    protected $casts = [
        "quantity" => "integer",
        "weight" => "float",
    ];

    /**
     * Get the shipping that owns the item
     */
    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

    /**
     * Calculate the total weight for this item
     *
     * @return float
     */
    public function getTotalWeightAttribute(): float
    {
        return $this->quantity * $this->weight;
    }
}
