<?php

namespace App\Enums\Shipping;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ShippingStatus: string implements HasLabel, HasColor
{
    case Draft = "draft";
    case Pending = "pending";
    case Picked_Up = "picked_up";
    case In_Transit = "in_transit";
    case Delivered = "delivered";
    case Cancelled = "cancelled";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __("dashboard.Draft"),
            self::Pending => __("dashboard.Pending"),
            self::Picked_Up => __("dashboard.Picked_Up"),
            self::In_Transit => __("dashboard.In_Transit"),
            self::Delivered => __("dashboard.Delivered"),
            self::Cancelled => __("dashboard.Cancelled"),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => "gray",
            self::Pending => "warning",
            self::Picked_Up => "info",
            self::In_Transit => "primary",
            self::Delivered => "success",
            self::Cancelled => "danger",
        };
    }
}
