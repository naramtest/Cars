<?php

namespace App\Services\Payment\Providers;

use App\Enums\PaymentStatus;
use App\Http\Webhooks\WebhookHandlerFactory;
use App\Models\Payment;
use App\Services\Payment\PaymentProviderInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\PaymentLink;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentProvider implements PaymentProviderInterface
{
    public function __construct(protected string $apiKey)
    {
        Stripe::setApiKey($this->apiKey);
    }

    public function getProviderName(): string
    {
        return "stripe";
    }

    public function supportsPaymentLinks(): bool
    {
        return true;
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentLink(Payment $payment): ?string
    {
        $payable = $payment->payable;

        // Generate product name based on the payable model
        $productName = $this->getProductName($payable);

        // Create the payment link
        try {
            $paymentLink = PaymentLink::create([
                "line_items" => [
                    [
                        "price_data" => [
                            "currency" => strtolower($payment->currency_code),
                            "product_data" => [
                                "name" => $productName,
                            ],
                            "unit_amount" => $payment->amount,
                        ],
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
                // Store our payment ID in metadata for webhook matching
                "metadata" => [
                    "payment_id" => $payment->id,
                ],
            ]);

            // Update the payment with the provider ID
            $payment->provider_id = $paymentLink->id;
            $payment->save();

            return $paymentLink->url;
        } catch (Exception $e) {
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
    public function getPaymentFormData(Payment $payment): array
    {
        // For implementations using Stripe Elements
        // This would create a client secret for use with Stripe Elements
        try {
            $paymentIntent = PaymentIntent::create([
                "amount" => $payment->amount,
                "currency" => strtolower($payment->currency_code),
                "metadata" => [
                    "payment_id" => $payment->id,
                ],
            ]);

            return [
                "client_secret" => $paymentIntent->client_secret,
                "payment_intent_id" => $paymentIntent->id,
            ];
        } catch (Exception $e) {
            Log::error(
                "Failed to create Stripe payment intent: " . $e->getMessage(),
                [
                    "payment_id" => $payment->id,
                    "error" => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    public function checkPaymentStatus(Payment $payment): string
    {
        // If no provider ID, it's still pending
        if (!$payment->provider_id) {
            return PaymentStatus::PENDING->value;
        }

        try {
            // For payment links
            if (str_starts_with($payment->provider_id, "plink_")) {
                $paymentLink = PaymentLink::retrieve($payment->provider_id);

                // Check basic status
                if ($paymentLink->active) {
                    return PaymentStatus::PENDING->value;
                }
            }
            // For payment intents (direct payment)
            elseif (str_starts_with($payment->provider_id, "pi_")) {
                $paymentIntent = PaymentIntent::retrieve($payment->provider_id);

                return match ($paymentIntent->status) {
                    "succeeded" => PaymentStatus::PAID->value,
                    "processing" => PaymentStatus::PROCESSING->value,
                    "requires_payment_method",
                    "requires_confirmation",
                    "requires_action"
                        => PaymentStatus::PENDING->value,
                    "canceled" => PaymentStatus::CANCELED->value,
                    default => $payment->status->value,
                };
            }

            // Default to current status if we can't determine
            return $payment->status->value;
        } catch (Exception $e) {
            Log::error(
                "Failed to check Stripe payment status: " . $e->getMessage(),
                [
                    "payment_id" => $payment->id,
                    "error" => $e->getMessage(),
                ]
            );

            return $payment->status->value;
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->getContent();
        $sigHeader = $request->header("Stripe-Signature");
        $endpointSecret = config("payment.providers.stripe.webhook_secret");

        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );

            // Use the WebhookHandlerFactory to get the appropriate handler
            $factory = app(WebhookHandlerFactory::class);
            $handler = $factory->getHandler("stripe", $event->type);

            if ($handler) {
                return $handler->handle($event->data->object);
            }

            return [
                "status" => "ignored",
                "type" => $event->type,
                "reason" => "no_handler_found",
            ];
        } catch (SignatureVerificationException $e) {
            Log::error(
                "Invalid signature in Stripe webhook: " . $e->getMessage()
            );
            return ["status" => "error", "message" => "Invalid signature"];
        } catch (Exception $e) {
            Log::error("Error handling Stripe webhook: " . $e->getMessage());
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
}
