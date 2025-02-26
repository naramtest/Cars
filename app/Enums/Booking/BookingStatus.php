<?php

namespace App\Enums\Booking;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasLabel, HasColor
{
    case Cancelled = "cancelled";
    case Completed = "completed";
    case Active = "active";
    case Pending = "pending";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Cancelled => __("dashboard.Cancelled"),
            self::Completed => __("dashboard.Completed"),
            self::Active => __("dashboard.is_active"),
            self::Pending => __("dashboard.Pending"),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Cancelled => "danger",
            self::Completed => "success",
            self::Active => "warning",
            self::Pending => "gray",
        };
    }
}
