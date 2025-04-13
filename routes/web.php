<?php

use App\Http\Controllers\DriverActionController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Settings\InfoSettings;
use Illuminate\Support\Facades\Route;

Route::get("/whatsapp/contact", function (InfoSettings $infoSettings) {
    return redirect()->away(
        "https://wa.me/$infoSettings->support_whatsapp_number"
    );
})->name("whatsapp.contact");

// Add this to routes/web.php
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
