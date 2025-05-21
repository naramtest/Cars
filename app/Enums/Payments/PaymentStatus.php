<?php

namespace App\Enums\Payments;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case PENDING = "pending";
    case PROCESSING = "processing";
    case PAID = "paid";
    case FAILED = "failed";
    case CANCELED = "canceled";
    case REFUNDED = "refunded";

    public function getColor(): string|array|null
    {
        return match ($this) {
            PaymentStatus::PAID => "success",
            PaymentStatus::PENDING => "warning",
            PaymentStatus::PROCESSING => "info",
            PaymentStatus::FAILED, PaymentStatus::CANCELED => "danger",
            PaymentStatus::REFUNDED => "gray",
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            PaymentStatus::PAID => "Paid",
            PaymentStatus::PENDING => "Pending",
            PaymentStatus::PROCESSING => "Processing",
            PaymentStatus::FAILED => "Failed",
            PaymentStatus::CANCELED => "Canceled",
            PaymentStatus::REFUNDED => "Refunded",
        };
    }
}
