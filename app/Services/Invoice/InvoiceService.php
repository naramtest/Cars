<?php

namespace App\Services\Invoice;

use App\Models\Payment;
use URL;

class InvoiceService
{
    /**
     * Generate a secure token for invoice access
     */
    public function generateInvoiceToken(Payment $payment): string
    {
        $route = URL::temporarySignedRoute(
            "payment.invoice", // route name
            now()->addMonth(), // expiration
            ["payment" => $payment->id]
        );
        return getQuery($route);
    }
}
