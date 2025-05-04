<?php

namespace App\Services\Payments\Providers;

use App\Enums\Payments\PaymentType;
use App\Models\Payment;
use App\Services\Payments\PaymentProviderInterface;
use Cache;
use Log;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentLink;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class StripePaymentLinksProvider implements PaymentProviderInterface
{
    public function getProviderName(): PaymentType
    {
        return PaymentType::STRIPE_LINK;
    }

    /**
     * @throws ApiErrorException
     */
    public function pay(Payment $payment): Payment
    {
        $this->bootStripe();
        dd($this->createPaymentLink($payment));
        return $payment;
    }

    protected function bootStripe(): void
    {
        Stripe::setApiKey(config("services.stripe.secret"));
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentLink(Payment $payment): ?string
    {
        $payable = $payment->payable;
        $productName = $this->getProductName($payable);
        $payableType = class_basename($payable);

        try {
            // Check if we have a product for this type
            $productId = $this->getOrCreateProduct($payableType, $productName);

            // Get or create price for this amount
            $priceId = $this->getOrCreatePrice(
                $productId,
                $payment->amount,
                $payment->currency_code
            );

            // Create the payment link with the price
            $paymentLink = PaymentLink::create([
                "line_items" => [
                    [
                        "price" => $priceId,
                        "quantity" => 1,
                    ],
                ],
                "after_completion" => [
                    "type" => "redirect",
                    "redirect" => [
                        "url" => route("payment.success", [
                            "payment" => $payment->id,
                        ]),
                    ],
                ],
                "metadata" => [
                    "payment_id" => $payment->id,
                ],
            ]);

            return $paymentLink->url;
        } catch (\Exception $e) {
            Log::error(
                "Failed to create Stripe payment link: " . $e->getMessage(),
                [
                    "payment_id" => $payment->id,
                    "error" => $e->getMessage(),
                ]
            );
            throw $e;
        }
    }

    protected function getProductName($payable): string
    {
        $modelType = class_basename($payable);
        $identifier = $payable->reference_number ?? $payable->id;

        return "Payment for $modelType #$identifier";
    }

    /**
     * @throws ApiErrorException
     */
    protected function getOrCreateProduct($payableType, $productName): string
    {
        $cacheKey = "stripe_product_$payableType";
        $productId = Cache::get($cacheKey);

        if (!$productId) {
            $product = Product::create([
                "name" => $productName,
            ]);
            $productId = $product->id;
            Cache::put($cacheKey, $productId, now()->addDays(30));
        }

        return $productId;
    }

    /**
     * @throws ApiErrorException
     */
    protected function getOrCreatePrice($productId, $amount, $currency): string
    {
        $cacheKey = "stripe_price_{$productId}_{$amount}_$currency";
        $priceId = Cache::get($cacheKey);

        if (!$priceId) {
            // Create new price
            $price = Price::create([
                "product" => $productId,
                "unit_amount" => $amount,
                "currency" => strtolower($currency),
            ]);

            $priceId = $price->id;
            // Cache it for future use
            Cache::put($cacheKey, $priceId, now()->addDays(30));
        }

        return $priceId;
    }
}
