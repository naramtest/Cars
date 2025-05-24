<?php

namespace App\Http\Controllers;

use App\Enums\Payments\PaymentStatus;
use App\Models\Payment;
use App\Settings\InfoSettings;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function success(Request $request, InfoSettings $infoSettings)
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
        // Always set to PROCESSING first, let webhook update to PAID when confirmed
        if ($payment->status === PaymentStatus::PENDING) {
            $payment->status = PaymentStatus::PROCESSING;
            $payment->save();
        }

        return view("payment.success", [
            "payment" => $payment,
            "info" => $infoSettings,
        ]);
    }

    public function showPayment(Payment $payment)
    {
        if ($payment->status->isFinal()) {
            return view("payment.final", ["payment" => $payment]);
        }
        return view("payment.pay", ["payment" => $payment]);
    }
}
