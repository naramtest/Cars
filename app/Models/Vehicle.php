<?php

namespace App\Models;

use App\Enums\TypesEnum;
use App\Enums\Vehicle\FuelType;
use App\Enums\Vehicle\GearboxType;
use App\Models\Abstract\MoneyModel;
use App\Traits\HasNotifications;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;
use Storage;

class Vehicle extends MoneyModel
{
    use SoftDeletes;
    use HasNotifications;

    protected $fillable = [
        "name",
        "type_id",
        "model",
        "engine_number",
        "engine_type",
        "license_plate",
        "registration_expiry_date",
        "daily_rate",
        "currency_code",
        "year_of_first_immatriculation",
        "gearbox",
        "fuel_type",
        "number_of_seats",
        "kilometer",
        "options",
        "document",
        "notes",
        "inspection_period_days",
        "notify_before_inspection",
        "next_inspection_date",
    ];

    protected $casts = [
        "registration_expiry_date" => "date",
        "next_inspection_date" => "date",
        "gearbox" => GearboxType::class,
        "fuel_type" => FuelType::class,
        "kilometer" => "integer",
        "options" => "array",
        "notify_before_inspection" => "boolean",
    ];

    protected $appends = ["formatted_daily_rate", "daily_rate_decimal"];

    protected static function booted(): void
    {
        static::forceDeleted(function (Vehicle $vehicle) {
            if ($vehicle->document) {
                Storage::disk("public")->delete($vehicle->document);
            }
        });

        static::creating(function (Vehicle $vehicle) {
            if (empty($vehicle->next_inspection_date)) {
                $vehicle->next_inspection_date = now();
            }
            if (empty($vehicle->currency_code)) {
                $vehicle->currency_code = $vehicle->currencyService->getDefaultCurrency();
            }
        });
    }

    public function getDailyRateMoneyAttribute(): Money
    {
        return $this->currencyService->money(
            $this->daily_rate,
            $this->currency_code
        );
    }

    /**
     * Get the formatted daily rate
     *
     * @return string
     */
    public function getFormattedDailyRateAttribute(): string
    {
        return $this->currencyService->format($this->daily_rate_money);
    }

    /**
     * Get the daily rate as a decimal
     *
     * @return float
     */
    public function getDailyRateDecimalAttribute(): float
    {
        return $this->currencyService->convertToDecimal(
            $this->daily_rate,
            $this->currency_code
        );
    }

    public function types(): MorphToMany
    {
        return $this->morphToMany(Type::class, "typeable")
            ->where("type", TypesEnum::VEHICLE)
            ->withTimestamps();
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get inspections for this vehicle.
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    // Calculate days until next inspection

    public function getDaysUntilNextInspectionAttribute(): ?int
    {
        if (!$this->next_inspection_date) {
            return null;
        }

        return ceil(Carbon::now()->diffInDays($this->next_inspection_date));
    }
}
