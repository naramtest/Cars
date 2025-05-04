<?php

namespace App\Services\Payments\Providers\Stripe;

use Stripe\Stripe;

abstract class StripeProvider
{
    protected function bootStripe(): void
    {
        Stripe::setApiKey(config("payment.providers.stripe.secret"));
    }
}
