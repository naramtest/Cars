<?php

namespace App\Models;

use App\Enums\Inspection\InspectionStatus;
use App\Enums\Inspection\RepairStatus;
use App\Models\Abstract\MoneyModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;
use Storage;

class Inspection extends MoneyModel
{
    use SoftDeletes;

    protected $fillable = [
        "vehicle_id",
        "inspection_by",
        "inspection_date",
        "status",
        "repair_status",
        "notes",
        "meter_reading_km",
        "incoming_date",
        "amount",
        "currency_code",
        "receipt",
        "checklist",
    ];

    protected $casts = [
        "inspection_date" => "date",
        "incoming_date" => "date",
        "status" => InspectionStatus::class,
        "repair_status" => RepairStatus::class,
        "checklist" => "array",
    ];

    protected $appends = ["formatted_amount"];

    protected static function booted(): void
    {
        static::forceDeleted(function (Inspection $inspection) {
            if ($inspection->receipt) {
                Storage::disk("public")->delete($inspection->receipt);
            }
        });

        // Set default currency_code if not provided
        static::creating(function (Inspection $inspection) {
            if (empty($inspection->currency_code)) {
                $inspection->currency_code = $inspection->currencyService->getDefaultCurrency();
            }
        });
    }

    /**
     * Get the vehicle that owns the inspection.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get inspection types.
     */
    public function types(): MorphToMany
    {
        return $this->morphToMany(Type::class, "typeable")
            ->where("type", "Inspection")
            ->withTimestamps();
    }

    /**
     * Get the amount as a Money object.
     *
     * @return Money|null
     */
    public function getAmountMoneyAttribute(): ?Money
    {
        if (is_null($this->amount)) {
            return null;
        }

        return $this->currencyService->money(
            $this->amount,
            $this->currency_code
        );
    }

    /**
     * Get the formatted amount.
     *
     * @return string|null
     */
    public function getFormattedAmountAttribute(): ?string
    {
        if (is_null($this->amount_money)) {
            return null;
        }

        return $this->currencyService->format($this->amount_money);
    }
}
