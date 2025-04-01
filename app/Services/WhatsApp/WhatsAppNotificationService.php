<?php

namespace App\Services\WhatsApp;

use Log;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsAppNotificationService
{
    protected $whatsAppClient;

    public function __construct(WhatsAppCloudApi $whatsAppClient)
    {
        $this->whatsAppClient = $whatsAppClient;
    }

    public function send(string $notificationClass, $data, $recipient = null)
    {
        // Check if this notification type is enabled
        if (!$this->isEnabled($notificationClass)) {
            Log::info("Notification skipped (disabled): $notificationClass");
            return false;
        }

        $handler = $this->resolveHandler($notificationClass);

        if (!$handler) {
            Log::warning(
                "No handler found for notification type: $notificationClass"
            );
            return false;
        }

        return $handler->send($data, $recipient);
    }

    protected function isEnabled(string $notificationType)
    {
        //TODO: add setting page to control notifications
        return true;
    }

    protected function resolveHandler(string $handlerClass)
    {
        if (!$handlerClass || !class_exists($handlerClass)) {
            return null;
        }

        return app($handlerClass);
    }
}
