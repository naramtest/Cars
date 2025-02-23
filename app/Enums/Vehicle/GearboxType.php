<?php

namespace App\Enums\Vehicle;

use Filament\Support\Contracts\HasLabel;

enum GearboxType: string implements HasLabel
{
    case Automatic = "Automatic";
    case Manual = "Manual";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Automatic => __("dashboard.Automatic"),
            self::Manual => __("dashboard.Manual"),
        };
    }
}
