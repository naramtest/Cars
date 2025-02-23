<?php

namespace App\Models;

use App\Enums\Vehicle\FuelType;
use App\Enums\Vehicle\GearboxType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        "daily_rate" => "decimal:2",
        "kilometer" => "integer",
        "options" => "array",
    ];

    protected static function booted(): void
    {
        static::forceDeleted(function (Vehicle $vehicle) {
            if ($vehicle->document) {
                Storage::disk("public")->delete($vehicle->document);
            }
        });
    }

    public function types(): MorphToMany
    {
        return $this->morphToMany(Type::class, "typeable")
            ->where("type", "Vehicle")
            ->withTimestamps();
    }
}
