<?php

namespace App\Models;

use App\Enums\TypesEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Type extends Model
{
    use SoftDeletes;
    use HasTranslations;

    public array $sortable = [
        "order_column_name" => "order",
    ];

    public array $translatable = ["name", "description"];

    protected $casts = [
        "is_visible" => "boolean",
        "type" => TypesEnum::class,
    ];

    protected $fillable = [
        "name",
        "slug",
        "description",
        "order",
        "is_visible",
        "type",
    ];

    public function determineTitleColumnName(): string
    {
        return "name";
    }

    public function scopeVisible($query)
    {
        return $query->where("is_visible", 1);
    }

    public function scopeByOrder($query)
    {
        return $query->orderBy("order");
    }

    public function vehicles(): MorphToMany
    {
        return $this->morphedByMany(
            Vehicle::class,
            "typeable"
        )->withTimestamps();
    }

    public function expenses(): MorphToMany
    {
        return $this->morphedByMany(
            Expense::class,
            "typeable"
        )->withTimestamps();
    }

    public function inspections(): MorphToMany
    {
        return $this->morphedByMany(
            Inspection::class,
            "typeable"
        )->withTimestamps();
    }
}
