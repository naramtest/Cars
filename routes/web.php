<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DriverActionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhooksController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view("welcome");
});

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

Route::controller(WebhooksController::class)->group(function () {
    Route::post("/webhooks/stripe", "stripe")->name("webhooks.stripe");
});

Route::controller(PaymentController::class)->group(function () {
    Route::get("/payment/{payment}/pay", "showPayment")->name(
        "payment.pay.show"
    );
    Route::get("/payments/success", "success")->name("payment.success");
});
