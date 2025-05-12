<?php

namespace App\Enums;

use Filament\Resources\Components\Tab;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;

enum ReservationStatus: string implements HasLabel, HasColor
{
    case Pending = "pending";
    case Confirmed = "confirmed";
    case Active = "active";
    case Completed = "completed";
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
