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

    public function stripePay(Payment $payment)
    {
        return view("payment.checkout", ["payment" => $payment]);
    }
}
