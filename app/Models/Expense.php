<?php

namespace App\Models;

use App\Enums\TypesEnum;
use App\Models\Abstract\MoneyModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;
use Storage;

class Expense extends MoneyModel
{
    use SoftDeletes;

    protected $fillable = [
        "title",
        "vehicle_id",
        "expense_date",
        "amount",
        "currency_code",
        "receipt",
        "notes",
    ];

    protected $casts = [
        "expense_date" => "date",
    ];

    protected $appends = ["formatted_amount", "amount_decimal"];

    protected static function booted(): void
    {
        static::forceDeleted(function (Expense $expense) {
            if ($expense->receipt) {
                Storage::disk("public")->delete($expense->receipt);
            }
        });

        // Set default currency_code if not provided
        static::creating(function (Expense $expense) {
            if (empty($expense->currency_code)) {
                $expense->currency_code = $expense->currencyService->getDefaultCurrency();
            }
        });
    }

    /**
     * Get the amount as a Money object
     *
     * @return Money
     */
    public function getAmountMoneyAttribute(): Money
    {
        return $this->currencyService->money(
            $this->amount,
            $this->currency_code
        );
    }

    /**
     * Get the formatted amount
     *
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return $this->currencyService->format($this->amount_money);
    }

    /**
     * Get the amount as a decimal
     *
     * @return float
     */
    public function getAmountDecimalAttribute(): float
    {
        return $this->currencyService->convertToDecimal(
            $this->amount,
            $this->currency_code
        );
    }

    /**
     * Get the vehicle that the expense belongs to.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the types associated with the expense.
     */
    public function types(): MorphToMany
    {
        return $this->morphToMany(Type::class, "typeable")
            ->where("type", TypesEnum::EXPENSE)
            ->withTimestamps();
    }
}
