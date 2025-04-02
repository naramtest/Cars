<?php

namespace App\Enums\Shipping;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ShippingStatus: string implements HasLabel, HasColor
{
    case Pending = "pending";
    case Confirmed = "confirmed";
    case Picked_Up = "picked_up";
    case Delivered = "delivered";
    case Cancelled = "cancelled";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => __("dashboard.Pending"),
            self::Picked_Up => __("dashboard.Picked_Up"),
            self::Delivered => __("dashboard.Delivered"),
            self::Cancelled => __("dashboard.Cancelled"),
            self::Confirmed => __("dashboard.Confirmed"),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => "warning",
            self::Picked_Up => "info",
            self::Delivered => "success",
            self::Cancelled => "danger",
            self::Confirmed => "primary",
        };
    }
}
