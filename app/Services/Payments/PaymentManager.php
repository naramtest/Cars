<?php

namespace App\Services\Payments;

use App\Enums\Payments\PaymentType;
use App\Services\Payments\Providers\StripePaymentLinksProvider;

class PaymentManager
{
    public function driver(PaymentType $driver): PaymentService
    {
        $provider = match ($driver) {
            PaymentType::STRIPE_LINK => app(StripePaymentLinksProvider::class),
        };

        return new PaymentService($provider);
    }
}
