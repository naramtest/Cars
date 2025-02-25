<?php

namespace App\Models;

use App\Enums\Addon\BillingType;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use Money\Money;

class Addon extends Model
{
    protected $fillable = [
        "name",
        "price",
        "billing_type",
        "description",
        "is_active",
    ];

    protected $casts = [
        "billing_type" => BillingType::class,
        "is_active" => "boolean",
    ];

    /**
     * Get the price as a Money object.
     *
     * @return Money
     */
    public function getMoneyPriceAttribute(): Money
    {
        return new Money($this->price, new Currency($this->currency));
    }

    /**
     * Set the price from a decimal value.
     *
     * @param string|float $value
     * @return void
     */
    public function setPriceAttribute($value): void
    {
        $currencies = new ISOCurrencies();
        $parser = new DecimalMoneyParser($currencies);
        $money = $parser->parse(
            (string) $value,
            new Currency($this->currency ?? "USD")
        );
        $this->attributes["price"] = $money->getAmount();
    }

    /**
     * Get the formatted price.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        $moneyFormatter = new IntlMoneyFormatter(
            new \NumberFormatter(
                app()->getLocale(),
                \NumberFormatter::CURRENCY
            ),
            new ISOCurrencies()
        );

        return $moneyFormatter->format($this->money_price);
    }

    /**
     * Scope a query to only include active addons.
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }
}
