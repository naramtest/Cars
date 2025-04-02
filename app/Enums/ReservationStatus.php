<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReservationStatus: string implements HasLabel, HasColor
{
    case Cancelled = "cancelled";
    case Completed = "completed";
    case Active = "active";
    case Pending = "pending";
    case Confirmed = "confirmed";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Cancelled => __("dashboard.Cancelled"),
            self::Completed => __("dashboard.Completed"),
            self::Active => __("dashboard.is_active"),
            self::Pending => __("dashboard.Pending"),
            self::Confirmed => __("dashboard.Confirmed"),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Cancelled => "danger",
            self::Completed => "success",
            self::Active => "warning",
            self::Pending => "gray",
            self::Confirmed => "primary",
        };
    }
}
