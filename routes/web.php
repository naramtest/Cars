<?php

use App\Models\Booking;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Support\Facades\Route;

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
