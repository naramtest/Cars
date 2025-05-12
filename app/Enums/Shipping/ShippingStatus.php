<?php

namespace App\Enums\Shipping;

use Filament\Resources\Components\Tab;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;

enum ShippingStatus: string implements HasLabel, HasColor
{
    case Pending = "pending";
    case Confirmed = "confirmed";
    case Picked_Up = "picked_up";
    case Delivered = "delivered";
    case Cancelled = "cancelled";

    public static function tabs(): array
    {
        $tabs = [
            "All" => Tab::make(),
        ];
        foreach (self::cases() as $status) {
            $tabs[$status->getLabel()] = Tab::make()->modifyQueryUsing(
                fn(Builder $query) => $query->where("status", "=", $status)
            );
        }
        return $tabs;
    }

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
