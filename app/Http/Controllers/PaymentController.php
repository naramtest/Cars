<?php

namespace App\Http\Controllers;

use App\Enums\Payments\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        $paymentIntentId = $request->has("payment_id")
            ? $request->get("payment_id")
            : $request->get("payment_intent"); //This is for stripe
        //TODO: if Payment  is null show an error message

        if (!$paymentIntentId) {
            abort(404);
        }
        $payment = Payment::where("provider_id", $paymentIntentId)->first();
        if (!$payment) {
            abort(404);
        }
        $payment->status = PaymentStatus::PROCESSING;
        $payment->save();
        return view("payment.success", ["payment" => $payment]);
    }

    public function showPayment(Payment $payment)
    {
        if ($payment->status->isFinal()) {
            return view("payment.final", ["payment" => $payment]);
        }
        return view("payment.pay", ["payment" => $payment]);
    }
}
