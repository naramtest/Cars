<?php

namespace App\Enums\Inspection;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InspectionStatus: string implements HasLabel, HasColor
{
    case Completed = "completed";
    case InProgress = "in_progress";
    case ConditionalPass = "conditional_pass";
    case OnHold = "on_hold";
    case Pending = "pending";
    case Rejected = "rejected";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Completed => __("dashboard.Completed"),
            self::InProgress => __("dashboard.In Progress"),
            self::ConditionalPass => __("dashboard.Conditional Pass"),
            self::OnHold => __("dashboard.On Hold"),
            self::Pending => __("dashboard.Pending"),
            self::Rejected => __("dashboard.Rejected"),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Completed => "success",
            self::InProgress => "warning",
            self::ConditionalPass => "info",
            self::OnHold => "gray",
            self::Pending => "primary",
            self::Rejected => "danger",
        };
    }
}
