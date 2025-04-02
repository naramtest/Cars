<?php

use App\Http\Controllers\WhatsAppWebhookController;
use App\Models\Booking;
use App\Services\WhatsApp\Driver\Booking\DriverBookingNewHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use App\Settings\InfoSettings;
use Illuminate\Support\Facades\Route;

Route::get("/webhook", [WhatsAppWebhookController::class, "verify"]);
Route::post("/webhook", [WhatsAppWebhookController::class, "handleWebhook"]);
Route::get("/whatsapp/contact", function (InfoSettings $infoSettings) {
    return redirect()->away(
        "https://wa.me/$infoSettings->support_whatsapp_number"
    );
});
Route::get("/test", function (WhatsAppNotificationService $whatsAppService) {
    $result = $whatsAppService->send(
        DriverBookingNewHandler::class,
        Booking::first(),
        "+971562065970"
    );
    dd($result);
});

Route::get("/", function () {
    return view("welcome");
});
