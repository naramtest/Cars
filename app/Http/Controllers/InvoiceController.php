<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Invoice\InvoiceService;
use Exception;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    public function downloadInvoice(Request $request)
    {
        // Get the signed token from request
        $paymentId = $request->get("payment");
        if (!$paymentId) {
            abort(404, "Invalid invoice link");
        }

        // Find the payment
        $payment = Payment::find($paymentId);
        if (!$payment) {
            abort(404, "Payment not found");
        }

        // Check if payment is paid
        if (!$payment->isPaid()) {
            abort(404, "Invoice not available - payment not completed");
        }

        try {
            // Generate and download the invoice
            return $this->invoiceService->downloadInvoice($payment);
        } catch (Exception) {
            abort(500, "Failed to generate invoice. Please try again later.");
        }
    }

    public function previewInvoice(Payment $payment)
    {
        try {
            return $this->invoiceService->streamInvoice($payment);
        } catch (Exception) {
            abort(500, "Failed to generate invoice preview.");
        }
    }
}
