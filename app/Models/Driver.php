<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;

class Driver extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "user_id",
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->user ? $this->user->name : "";
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function shippings(): HasMany
    {
        return $this->hasMany(Shipping::class);
    }
}
