<?php

namespace App\Http\Controllers;

use App\Enums\Payments\PaymentStatus;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\RentResource;
use App\Filament\Resources\ShippingResource;
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

    public function adminRedirect(Payment $payment)
    {
        $model = $payment->payable;
        $route = match (class_basename($model)) {
            "Booking" => BookingResource::getUrl("edit", [
                "record" => $model->id,
            ]),
            "Rent" => RentResource::getUrl("edit", ["record" => $model->id]),
            "Shipping" => ShippingResource::getUrl("edit", [
                "record" => $model->id,
            ]),
            default => "/admin",
        };
        return redirect($route);
    }
}
