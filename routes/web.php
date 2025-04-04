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
});

Route::get("/", function () {
    $waba_id = config("services.whatsapp.waba_id");
    $api_version = config("services.whatsapp.api_version");
    $request = Http::withHeaders([
        "Authorization" => "Bearer " . config("services.whatsapp.token"),
        "Content-Type" => "application/json",
    ])->get(
        "https://graph.facebook.com/{$api_version}/{$waba_id}/message_templates"
    );
    dd($request->json());
});
