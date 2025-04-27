<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DriverActionController;
use App\Http\Controllers\WhatsAppWebhookController;
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
    return view("welcome");
});
