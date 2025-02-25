<?php

namespace App\Models;

use App\Enums\Addon\BillingType;
use App\Services\Currency\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use Money\Money;

class Addon extends Model
{
    protected $fillable = [
        "name",
        "price",
        "currency_code",
        "billing_type",
        "description",
        "is_active",
    ];

    protected $casts = [
        "billing_type" => BillingType::class,
        "is_active" => "boolean",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ["price"];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ["formatted_price", "price_decimal"];

    protected static function booted(): void
    {
        // Set default currency_code if not provided
        static::creating(function (Addon $addon) {
            if (empty($addon->currency_code)) {
                $addon->currency_code = config("app.money_currency", "USD");
            }
        });
    }

    /**
     * Get the price as a Money object.
     *
     * @return Money
     */
    public function getPriceMoneyAttribute(): Money
    {
        return $this->currencyService()->money(
            $this->price,
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
     * Get the price as a decimal
     *
     * @return float
     */
    public function getPriceDecimalAttribute(): float
    {
        return $this->currencyService()->convertToDecimal(
            $this->price,
            $this->currency_code
        );
    }

    /**
     * Set the price from a decimal value.
     *
     * @param float|string $value
     * @return void
     */
    public function setPriceAttribute(float|string $value): void
    {
        $this->attributes["price"] = $this->currencyService()->convertToInteger(
            $value,
            $this->attributes["currency_code"] ?? null
        );
    }

    /**
     * Get the formatted price.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->currencyService()->format($this->price_money);
    }

    /**
     * Scope a query to only include active addons.
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }
}
