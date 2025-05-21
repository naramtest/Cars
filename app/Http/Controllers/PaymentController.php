<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Settings\InfoSettings;

class PaymentController extends Controller
{
    public function success(Payment $payment, InfoSettings $infoSettings)
    {
        return view("payment.success", ["payment" => $payment]);
    }
}
