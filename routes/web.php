<?php

use App\Http\Controllers\DriverActionController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Models\Booking;
use App\Services\WhatsApp\Customer\Booking\CBNewHandler;
use App\Services\WhatsApp\Driver\Booking\DBNewHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use App\Services\WhatsApp\WhatsAppUpdateTemplateService;
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
    Route::get("/driver/shipping/delivery/{token}", "confirmDelivery")->name(
        "shipping.driver.delivery"
    );
    Route::get("/driver/shipping/pickup/{token}", "confirmPickup")->name(
        "shipping.driver.pickup"
    );
    Route::post("/driver/shipping/process-delivery", "processDelivery")->name(
        "shipping.driver.process-delivery"
    );
});

Route::get("/webhook", [WhatsAppWebhookController::class, "verify"]);
Route::post("/webhook", [WhatsAppWebhookController::class, "handleWebhook"]);

Route::get("/test", function (WhatsAppNotificationService $whatsAppService) {
    $result = $whatsAppService->send(new DBNewHandler(), Booking::first());
    dd($result);
});

Route::get("/", function () {
    app(WhatsAppUpdateTemplateService::class)->updateTemplate(
        CBNewHandler::class
    );
});
