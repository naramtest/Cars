<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class PaymentController extends Controller
{
    public function success(Payment $payment)
    {
        dd($payment);
    }

    public function cancel(Payment $payment)
    {
        dd($payment);
    }
}
