<?php

namespace App\Services\Invoice;

use App\Models\Payment;

class InvoiceService
{
    /**
     * Generate invoice download URL with secure token
     */
    public function generateInvoiceUrl(Payment $payment): string
    {
        $token = $this->generateInvoiceToken($payment);

        return route("payment.invoice", [
            "token" => $token,
            "payment_id" => $payment->id,
        ]);
    }

    /**
     * Generate a secure token for invoice access
     */
    public function generateInvoiceToken(Payment $payment): string
    {
        $data = [
            "payment_id" => $payment->id,
            "customer_phone" => $payment->payable->getCustomer()->phone_number,
            "amount" => $payment->amount,
            "expires_at" => now()->addDays(30)->timestamp, // Token expires in 30 days
        ];

        return encrypt(json_encode($data));
    }

    /**
     * Verify the invoice access token
     */
    public function verifyInvoiceToken(string $token, Payment $payment): bool
    {
        try {
            $decrypted = decrypt($token);
            $data = json_decode($decrypted, true);

            // Check if token data is valid
            if (!$data || !isset($data["payment_id"], $data["expires_at"])) {
                return false;
            }

            // Check if token is expired
            if (now()->timestamp > $data["expires_at"]) {
                return false;
            }

            // Check if payment ID matches
            if ($data["payment_id"] != $payment->id) {
                return false;
            }

            // Verify customer phone matches (additional security)
            $customerPhone = $payment->payable->getCustomer()->phone_number;
            if ($data["customer_phone"] !== $customerPhone) {
                return false;
            }

            // Verify amount matches (prevent token reuse for different amounts)
            if ($data["amount"] != $payment->amount) {
                return false;
            }

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
