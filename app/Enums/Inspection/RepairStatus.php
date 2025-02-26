<?php

namespace App\Enums\Inspection;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RepairStatus: string implements HasLabel, HasColor
{
    case Completed = "completed";
    case InProgress = "in_progress";
    case NeedsRepair = "needs_repair";
    case OnHold = "on_hold";
    case Pending = "pending";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Completed => __("dashboard.Completed"),
            self::InProgress => __("dashboard.In Progress"),
            self::NeedsRepair => __("dashboard.Needs Repair"),
            self::OnHold => __("dashboard.On Hold"),
            self::Pending => __("dashboard.Pending"),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Completed => "success",
            self::InProgress => "warning",
            self::NeedsRepair => "danger",
            self::OnHold => "gray",
            self::Pending => "primary",
        };
    }
}
