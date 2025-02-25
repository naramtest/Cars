<?php

namespace App\Models;

use App\Enums\Vehicle\FuelType;
use App\Enums\Vehicle\GearboxType;
use App\Services\Currency\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;
use Storage;

class Vehicle extends Model
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
    ];

    protected $casts = [
        "registration_expiry_date" => "date",
        "year_of_first_immatriculation" => "date",
        "gearbox" => GearboxType::class,
        "fuel_type" => FuelType::class,
        "kilometer" => "integer",
        "options" => "array",
    ];

    protected $hidden = ["daily_rate"];

    protected $appends = ["formatted_daily_rate", "daily_rate_decimal"];

    protected static function booted(): void
    {
        static::forceDeleted(function (Vehicle $vehicle) {
            if ($vehicle->document) {
                Storage::disk("public")->delete($vehicle->document);
            }
        });

        // Set default currency_code if not provided
        static::creating(function (Vehicle $vehicle) {
            if (empty($vehicle->currency_code)) {
                $vehicle->currency_code = config("app.money_currency", "USD");
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
        return $this->currencyService()->money(
            $this->daily_rate,
            $this->currency_code ??
                $this->currencyService()->getDefaultCurrency()
        );
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
     * Get the formatted daily rate
     *
     * @return string
     */
    public function getFormattedDailyRateAttribute(): string
    {
        return $this->currencyService()->format($this->daily_rate_money);
    }

    /**
     * Get the daily rate as a decimal
     *
     * @return float
     */
    public function getDailyRateDecimalAttribute(): float
    {
        return $this->currencyService()->convertToDecimal(
            $this->daily_rate,
            $this->currency_code
        );
    }

    /**
     * Set the daily rate from a decimal value
     *
     * @param float|string $value
     * @return void
     */
    public function setDailyRateAttribute(float|string $value): void
    {
        $this->attributes[
            "daily_rate"
        ] = $this->currencyService()->convertToInteger(
            $value,
            $this->attributes["currency_code"] ?? null
        );
    }

    public function types(): MorphToMany
    {
        return $this->morphToMany(Type::class, "typeable")
            ->where("type", "Vehicle")
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
}
