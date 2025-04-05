<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Netflie\WhatsAppCloudApi\WebHook;

class WhatsAppWebhookController extends Controller
{
    // Verify webhook endpoint
    public function verify(Request $request)
    {
        $webhook = new WebHook();

        // Token from Meta App dashboard (you define it yourself)
        $verifyToken = "20250117154627N0SN";

        // Meta (Facebook) sends these parameters to verify webhook
        return response($webhook->verify($request->all(), $verifyToken));
    }

    // Handle incoming webhook notifications
    public function handleWebhook(Request $request)
    {
        $webhook = new WebHook();

        // Get raw payload
        $payload = $request->getContent();

        // Optional: log the incoming payload
        Log::info("WhatsApp Webhook Payload: " . $payload);

        // Decode the payload and read notification
        $notification = $webhook->read(json_decode($payload, true));

        // Handle the notification (Example: incoming message)
        if ($notification && $notification->type() === "message") {
            $message = $notification->message();

            // Example handling: log incoming message details
            Log::info("Incoming WhatsApp message", [
                "from" => $message->from(),
                "text" => $message->text(),
            ]);
        }

        // To handle multiple messages (batch)
        $notifications = $webhook->readAll(json_decode($payload, true));

        foreach ($notifications as $notif) {
            if ($notif->type() === "message") {
                $message = $notif->message();

                // Handle each message individually
                Log::info("Batch WhatsApp message", [
                    "from" => $message->from(),
                    "text" => $message->text(),
                ]);
            }
        }

        return response("EVENT_RECEIVED", 200);
    }
}
