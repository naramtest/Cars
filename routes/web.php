<?php

use App\Http\Controllers\WhatsAppWebhookController;
use App\Models\Booking;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Support\Facades\Route;

Route::get("/webhook", [WhatsAppWebhookController::class, "verify"]);
Route::post("/webhook", [WhatsAppWebhookController::class, "handleWebhook"]);

Route::get("/", function () {
    $whatsappService = new WhatsAppService();
    $booking = Booking::first();
    $result = $whatsappService->sendBookingConfirmation(
        $booking,
        "+971 562065970"
    );
    dd($result);
    if ($result) {
        return "Booking confirmation sent successfully!";
    } else {
        return "Failed to send booking confirmation.";
    }
});
