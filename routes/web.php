<?php

use App\Http\Controllers\WhatsAppWebhookController;
use App\Models\Booking;
use App\Services\WhatsApp\Driver\Booking\DBNewHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use App\Settings\InfoSettings;
use Illuminate\Support\Facades\Route;

Route::get("/whatsapp/contact", function (InfoSettings $infoSettings) {
    return redirect()->away(
        "https://wa.me/$infoSettings->support_whatsapp_number"
    );
});

Route::get("/bookings/complete/{booking:reference_number}", function (
    $booking
) {
    //TODO: add auth or pin check before changing the status of the booking
    dd($booking);
})->name("booking.driver.confirmation");

Route::get("/webhook", [WhatsAppWebhookController::class, "verify"]);
Route::post("/webhook", [WhatsAppWebhookController::class, "handleWebhook"]);

Route::get("/test", function (WhatsAppNotificationService $whatsAppService) {
    $result = $whatsAppService->send(new DBNewHandler(), Booking::first());
    dd($result);
});

Route::get("/", function () {
    //    dd(templateUrl(BookingResource::getUrl() . "/{{1}}"));
    //    return app(
    //        \App\Services\WhatsApp\WhatsAppTemplateService::class
    //    )->resolveTemplate(
    //        \App\Services\WhatsApp\Driver\Booking\DBUpdatedHandler::class
    //    );
});
