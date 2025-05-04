<?php

namespace App\Enums\Payments;

enum PaymentType: string
{
    case STRIPE_LINK = "stripe payment link";
}
