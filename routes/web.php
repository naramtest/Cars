<?php

use App\Enums\Payments\PaymentType;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DriverActionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Models\Booking;
use App\Services\Payments\PaymentManager;
use Illuminate\Support\Facades\Route;

Route::controller(ContactController::class)->group(function () {
    Route::get("/whatsapp/contact", "whatsapp")->name("whatsapp.contact");
    Route::get("/driver/contact/{driver}", "driver")->name("driver.contact");
});

Route::controller(DriverActionController::class)->group(function () {
    Route::get(
        "/bookings/complete/{booking:reference_number}",
        "confirmBookingCompletion"
    )->name("booking.driver.confirmation");

    Route::get(
        "shipping/delivery/{shipping:reference_number}",
        "confirmDelivery"
    )->name("shipping.driver.delivery");

    Route::get(
        "/shipping/pickup/{shipping:reference_number}",
        "confirmPickup"
    )->name("shipping.driver.pickup");

    Route::post("/driver/shipping/process-delivery", "processDelivery")->name(
        "shipping.driver.process-delivery"
    );
});

Route::get("/webhook", [WhatsAppWebhookController::class, "verify"]);
Route::post("/webhook", [WhatsAppWebhookController::class, "handleWebhook"]);

Route::get("/", function () {
    app(PaymentManager::class)
        ->driver(PaymentType::STRIPE_LINK)
        ->pay(Booking::first(), 10000, "AED");
    return view("welcome");
});

Route::get("/payments/{payment}/success", [
    PaymentController::class,
    "success",
])->name("payment.success");

Route::get("/payments/{payment}/cancel", [
    PaymentController::class,
    "cancel",
])->name("payment.cancel");
