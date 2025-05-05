<?php

namespace App\Services\Payments\Providers\Stripe;

use App\Enums\Payments\PaymentType;
use App\Models\Abstract\Payable;
use App\Models\Payment;
use App\Services\Payments\PaymentProviderInterface;
use Carbon\Carbon;
use Log;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentLink;

class StripePaymentLinksProvider extends StripeProvider implements
    PaymentProviderInterface
{
    public function __construct(protected StripeCatalogService $catalog) {}

    /**
     * @throws ApiErrorException
     */
    public function pay(Payment $payment): Payment
    {
        $this->bootStripe();
        $paymentLink = $this->createPaymentLink($payment);
        $payment->payment_link = $paymentLink->url;
        $payment->provider_id = $paymentLink->id;

        $payment->payment_link_expires_at = $this->getExpirationAt();
        return $payment;
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentLink(Payment $payment): ?PaymentLink
    {
        $payable = $payment->payable;
        $productName = $this->getProductName($payable);
        $type = $payable->reference_number;

        try {
            // Check if we have a product for this type
            $productId = $this->catalog->getOrCreateProduct(
                $type,
                $productName
            );
            $priceId = $this->catalog->getOrCreatePrice(
                $productId,
                $payment->amount,
                $payment->currency_code
            );

            // Create the payment link with the price
            return PaymentLink::create([
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
                "payment_intent_data" => [
                    "metadata" => [
                        "payment_id" => $payment->id,
                    ],
                ],
                "metadata" => [
                    "payment_id" => $payment->id,
                ],
            ]);
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

    protected function getProductName(Payable $payable): string
    {
        $modelType = class_basename($payable);
        $identifier = $payable->reference_number ?? $payable->id;

        return "Payment for $modelType  #$identifier";
    }

    /**
     * @return Carbon
     */
    public function getExpirationAt(): Carbon
    {
        return Carbon::now()->addDays(
            config("payment.providers.stripe.link_expiration_days", 7)
        );
    }

    public function getProviderName(): PaymentType
    {
        return PaymentType::STRIPE_LINK;
    }
}
