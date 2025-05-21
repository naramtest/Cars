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
        //        $endpointSecret = config("payment.providers.stripe.webhook");
        $endpointSecret =
            "whsec_a79ea5ac3ca1ace533d5c9b599b4fa263615a97e75a6a93604d1e65caec818ef";

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
        try {
            $payment = $this->findPaymentFromSession($session);
            return $payment->updatePaymentToPaid([
                "stripe_session_id" => $session->id,
            ]);
        } catch (Exception) {
            return ["status" => "error", "message" => "Payment not found"];
        }
    }

    /**
     * @throws ModelNotFoundException When payment cannot be found
     */
    protected function findPaymentFromSession($session): Payment
    {
        logger($session);

        if (isset($session->metadata->payment_id)) {
            return Payment::find($session->metadata->payment_id);
        }

        if (
            $payment = Payment::where(
                "provider_id",
                $session->payment_intent ?? $session->id
            )->first()
        ) {
            return $payment;
        }

        Log::error("Payment not found for Stripe session: " . $session->id);
        throw new ModelNotFoundException(
            "Payment not found for Stripe session: " . $session->id
        );
    }

    protected function handlePaymentIntentSucceeded($paymentIntent): array
    {
        try {
            $payment = $this->findPaymentFromSession($paymentIntent);
            // Update status to paid
            return $payment->updatePaymentToPaid([
                "stripe_payment_intent_id" => $paymentIntent->id,
            ]);
        } catch (Exception) {
            return ["status" => "error", "message" => "Payment not found"];
        }
    }

    protected function handlePaymentFailed($paymentIntent): array
    {
        try {
            $payment = $this->findPaymentFromSession($paymentIntent);

            return $payment->updatePaymentStatus(PaymentStatus::FAILED, [
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
        try {
            $payment = $this->findPaymentFromSession($session);

            return $payment->updatePaymentStatus(PaymentStatus::CANCELED, [
                "expired_at" => now()->toIso8601String(),
            ]);
        } catch (Exception) {
            return ["status" => "error", "message" => "Payment not found"];
        }
    }

    protected function handleRefund($charge): array
    {
        try {
            $payment = $this->findPaymentFromSession($charge);

            return $payment->updatePaymentStatus(PaymentStatus::REFUNDED, [
                "refunded_at" => now()->toIso8601String(),
                "refund_id" => $charge->refunds->data[0]->id ?? null,
            ]);
        } catch (Exception) {
            return ["status" => "error", "message" => "Payment not found"];
        }
    }
}
