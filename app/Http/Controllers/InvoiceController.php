<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Invoice\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function downloadInvoice(
        Request $request,
        InvoiceService $invoiceService
    ) {
        // Get the signed token from request
        $token = $request->get("token");
        $paymentId = $request->get("payment_id");
        if (!$token || !$paymentId) {
            abort(404, "Invalid invoice link");
        }

        // Find the payment
        $payment = Payment::find($paymentId);
        if (!$payment) {
            abort(404, "Payment not found");
        }

        // Verify the token
        if (!$invoiceService->verifyInvoiceToken($token, $payment)) {
            abort(403, "Unauthorized access to invoice");
        }

        // Check if payment is paid
        if (!$payment->isPaid()) {
            abort(404, "Invoice not available - payment not completed");
        }

        // TODO: Generate and return PDF invoice
        // For now, return a simple response
        return response()->json([
            "message" => "Invoice download will be implemented here",
            "payment" => [
                "id" => $payment->id,
                "amount" => $payment->formatted_amount,
                "reference" =>
                    $payment->payable->reference_number ??
                    $payment->payable->id,
                "type" => class_basename($payment->payable),
                "customer" => $payment->payable->getCustomer()->name,
            ],
        ]);
    }
}
