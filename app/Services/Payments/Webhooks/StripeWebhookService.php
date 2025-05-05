<?php

namespace App\Services\Payments\Webhooks;

use App\Enums\Payments\PaymentStatus;
use App\Models\Payment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookService
{
    public function handleWebhook(Request $request): array
    {
        $payload = $request->getContent();
        $sigHeader = $request->header("Stripe-Signature");
        $endpointSecret = config("payment.providers.stripe.webhook");

        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
            return match ($event->type) {
                "checkout.session.completed" => $this->handleCheckoutCompleted(
                    $event->data->object
                ),
                "payment_intent.succeeded"
                    => $this->handlePaymentIntentSucceeded(
                    $event->data->object
                ),
                // Failed payments
                "payment_intent.payment_failed" => $this->handlePaymentFailed(
                    $event->data->object
                ),
                "checkout.session.expired" => $this->handleSessionExpired(
                    $event->data->object
                ),
                // Refunds
                "charge.refunded" => $this->handleRefund($event->data->object),
                "payment_intent.canceled" => logger(
                    $event->data->object
                ), // Default case for unhandled events
                default => ["status" => "ignored", "type" => $event->type],
            };
        } catch (SignatureVerificationException | Exception $e) {
            Log::error("Error handling Stripe webhook: " . $e->getMessage());
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    protected function handleCheckoutCompleted($session): array
    {
        logger("checkout.session.completed");
        logger($session);

        try {
            $payment = $this->findPaymentFromSession($session);
            return $this->updatePaymentStatus($payment, PaymentStatus::PAID, [
                "stripe_session_id" => $session->id,
                "paid_at" => now()->toIso8601String(),
            ]);
        } catch (Exception) {
            return ["status" => "error", "message" => "Payment not found"];
        }
    }

    /**
     * @throws ModelNotFoundException When payment cannot be found
     */
    protected function findPaymentFromSession($session): ?Payment
    {
        if (isset($session->metadata->payment_id)) {
            return Payment::find($session->metadata->payment_id);
        }

        if ($session->payment_link) {
            return Payment::where(
                "provider_id",
                $session->payment_link
            )->first();
        }

        Log::error("Payment not found for Stripe session: " . $session->id);
        throw new ModelNotFoundException(
            "Payment not found for Stripe session: " . $session->id
        );
    }

    protected function updatePaymentStatus(
        Payment $payment,
        PaymentStatus $newStatus,
        array $metadataUpdates = []
    ): array {
        $oldStatus = $payment->status;
        $payment->status = $newStatus;

        // Update metadata
        $payment->metadata = array_merge(
            $payment->metadata ?? [],
            $metadataUpdates
        );
        $payment->save();
        return [
            "status" => "success",
            "payment_id" => $payment->id,
            "old_status" => $oldStatus->value,
            "new_status" => $newStatus->value,
        ];
    }

    protected function handlePaymentIntentSucceeded($paymentIntent): array
    {
        logger("payment_intent.succeeded");

        logger($paymentIntent);

        try {
            $payment = $this->findPaymentFromSession($paymentIntent);
            // Update status to paid
            return $this->updatePaymentStatus($payment, PaymentStatus::PAID, [
                "stripe_payment_intent_id" => $paymentIntent->id,
                "paid_at" => now()->toIso8601String(),
            ]);
        } catch (Exception) {
            return ["status" => "error", "message" => "Payment not found"];
        }
    }

    protected function handlePaymentFailed($paymentIntent): array
    {
        logger($paymentIntent);

        try {
            $payment = $this->findPaymentFromSession($paymentIntent);

            return $this->updatePaymentStatus($payment, PaymentStatus::FAILED, [
                "error_code" =>
                    $paymentIntent->last_payment_error->code ?? null,
                "error_message" =>
                    $paymentIntent->last_payment_error->message ?? null,
                "failed_at" => now()->toIso8601String(),
            ]);
        } catch (Exception) {
            return ["status" => "error", "message" => "Payment not found"];
        }
    }

    protected function handleSessionExpired($session): array
    {
        logger($session);

        try {
            $payment = $this->findPaymentFromSession($session);

            return $this->updatePaymentStatus(
                $payment,
                PaymentStatus::CANCELED,
                [
                    "expired_at" => now()->toIso8601String(),
                ]
            );
        } catch (Exception) {
            return ["status" => "error", "message" => "Payment not found"];
        }
    }

    protected function handleRefund($charge): array
    {
        logger($charge);

        try {
            $payment = $this->findPaymentFromSession($charge);

            return $this->updatePaymentStatus(
                $payment,
                PaymentStatus::REFUNDED,
                [
                    "refunded_at" => now()->toIso8601String(),
                    "refund_id" => $charge->refunds->data[0]->id ?? null,
                ]
            );
        } catch (Exception) {
            return ["status" => "error", "message" => "Payment not found"];
        }
    }
}
