<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "first_name",
        "last_name",
        "email",
        "phone_number",
        "gender",
        "birth_date",
        "address",
        "license_number",
        "issue_date",
        "expiration_date",
        "document",
        "license",
        "reference",
        "notes",
    ];

    protected $casts = [
        "birth_date" => "date",
        "issue_date" => "date",
        "expiration_date" => "date",
        "gender" => Gender::class,
    ];

    // Helper accessor to get full name

    protected static function booted(): void
    {
        static::forceDeleted(function (Driver $driver) {
            if ($driver->document) {
                Storage::disk("public")->delete($driver->document);
            }

            if ($driver->license) {
                Storage::disk("public")->delete($driver->license);
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
