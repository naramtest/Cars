<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class PaymentController extends Controller
{
    public function success(\Request $request)
    {
        dd($request);
        return view("payment.success", ["payment" => $payment]);
    }

    public function showPayment(Payment $payment)
    {
        return view("payment.pay", ["payment" => $payment]);
    }
}
