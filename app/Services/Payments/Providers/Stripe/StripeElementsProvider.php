<?php

namespace App\Services\Payments\Providers\Stripe;

use App\Enums\Payments\PaymentType;
use App\Models\Payment;
use App\Services\Payments\PaymentProviderInterface;
use Log;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;

class StripeElementsProvider extends StripeProvider implements
    PaymentProviderInterface
{
    /**
     * @throws ApiErrorException
     */
    public function pay(Payment $payment): Payment
    {
        $this->bootStripe();
        $paymentIntent = $this->createPaymentIntent($payment);

        $payment->provider_id = $paymentIntent->id;
        $payment->metadata = array_merge($payment->metadata ?? [], [
            "client_secret" => $paymentIntent->client_secret,
            "payment_intent_id" => $paymentIntent->id,
        ]);

        return $payment;
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentIntent(Payment $payment): PaymentIntent
    {
        try {
            $payable = $payment->payable;
            $customer = $payable->getCustomer();

            return PaymentIntent::create([
                "amount" => $payment->amount,
                "currency" => strtolower($payment->currency_code),
                "automatic_payment_methods" => [
                    "enabled" => true,
                    "allow_redirects" => "always",
                ],
                "description" => $this->getPaymentDescription($payable),
                "metadata" => [
                    "payment_id" => $payment->id,
                    "payable_type" => get_class($payable),
                    "payable_id" => $payable->id,
                    "customer_name" => $customer->name,
                    "reference_number" =>
                        $payable->reference_number ?? $payable->id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Failed to create Stripe PaymentIntent: " . $e->getMessage(),
                [
                    "payment_id" => $payment->id,
                    "error" => $e->getMessage(),
                ]
            );
            throw $e;
        }
    }

    protected function getPaymentDescription($payable): string
    {
        $modelType = class_basename($payable);
        $identifier = $payable->reference_number ?? $payable->id;

        return "Payment for $modelType #$identifier";
    }

    public function getProviderName(): PaymentType
    {
        return PaymentType::STRIPE_ELEMENTS;
    }
}
