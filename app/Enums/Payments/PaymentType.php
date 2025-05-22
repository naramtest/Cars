<?php

namespace App\Enums\Payments;

use Filament\Support\Contracts\HasLabel;

enum PaymentType: string implements HasLabel
{
    case STRIPE_LINK = "stripe payment link";
    case STRIPE_ELEMENTS = "stripe elements";
    case Cash = "Cash";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::STRIPE_ELEMENTS, self::STRIPE_LINK => "Stripe",
            self::Cash => "Cash",
        };
    }
}
