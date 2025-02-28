<?php

namespace App\Enums\Rent;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RentStatus: string implements HasLabel, HasColor
{
    case Cancelled = "cancelled";
    case Completed = "completed";
    case Active = "active";
    case Pending = "pending";
    case Draft = "draft";
    case Confirmed = "confirmed";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Cancelled => __("dashboard.Cancelled"),
            self::Completed => __("dashboard.Completed"),
            self::Active => __("dashboard.is_active"),
            self::Pending => __("dashboard.Pending"),
            self::Draft => __("dashboard.Draft"),
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
            self::Draft => "info",
            self::Confirmed => "primary",
        };
    }
}
