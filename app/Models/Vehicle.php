<?php

namespace App\Models;

use App\Enums\TypesEnum;
use App\Enums\Vehicle\FuelType;
use App\Enums\Vehicle\GearboxType;
use App\Models\Abstract\MoneyModel;
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
    ];

    protected $casts = [
        "registration_expiry_date" => "date",
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
            if (empty($vehicle->currency_code)) {
                $vehicle->currency_code = $vehicle->currencyService->getDefaultCurrency();
            }
        });
    }

    /**
     * Get the daily rate as a Money object
     *
     * @return Money
     */
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

    public function getNextInspectionDateAttribute(): ?Carbon
    {
        if (!$this->inspection_period_days) {
            return null;
        }
        $inspection = $this->inspections()->latest()->first();

        $start_date = $inspection->inspection_date ?? $this->created_at;

        return Carbon::parse($start_date)->addDays(
            $this->inspection_period_days
        );
    }

    // Calculate next inspection date based on period days

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

        return Carbon::now()->diffInDays($this->next_inspection_date, false);
    }

    //TODO: send notification when it is due

    // Check if inspection is due based on setting
    public function getIsInspectionDueAttribute(): bool
    {
        if (!$this->next_inspection_date || !$this->notify_before_inspection) {
            return false;
        }

        $notificationDays = config("inspections.notification_days_before", 7);
        return $this->days_until_next_inspection <= $notificationDays;
    }
}
