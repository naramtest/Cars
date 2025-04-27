<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;
    use HasRoles;

    protected $fillable = ["name", "email", "password"];

    protected $casts = [
        "email_verified_at" => "datetime",
        "password" => "hashed",
    ];

    protected $hidden = ["password", "remember_token"];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Check if the user is a driver
     */
    public function isDriver(): bool
    {
        return $this->driver()->exists();
    }

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }
}
