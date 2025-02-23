<?php

namespace App\Enums\Vehicle;

use Filament\Support\Contracts\HasLabel;

enum FuelType: string implements HasLabel
{
    case Essence = "Essence";
    case Diesel = "Diesel";
    case Hybrid = "Hybrid";
    case Electric = "Electric";
    case Gas = "Gas";
    case Petrol = "Petrol";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Essence => __("dashboard.Essence"),
            self::Diesel => __("dashboard.Diesel"),
            self::Hybrid => __("dashboard.Hybrid"),
            self::Electric => __("dashboard.Electric"),
            self::Gas => __("dashboard.Gas"),
            self::Petrol => __("dashboard.Petrol"),
        };
    }
}
