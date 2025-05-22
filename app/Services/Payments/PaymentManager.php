<?php

namespace App\Services\Payments;

use App\Enums\Payments\PaymentType;
use App\Services\Payments\Providers\Stripe\StripeElementsProvider;
use App\Services\Payments\Providers\Stripe\StripePaymentLinksProvider;

class PaymentManager
{
    public function driver(PaymentType $driver): PaymentService
    {
        $provider = match ($driver) {
            PaymentType::STRIPE_LINK => app(StripePaymentLinksProvider::class),
            PaymentType::STRIPE_ELEMENTS => app(StripeElementsProvider::class),
            PaymentType::Cash => throw new \Exception("To be implemented"),
        };

        return new PaymentService($provider);
    }
}
