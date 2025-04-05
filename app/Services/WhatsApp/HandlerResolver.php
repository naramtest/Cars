<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class HandlerResolver
{
    public static function resolve(
        string|WhatsAppAbstractHandler $handlerClass
    ): WhatsAppAbstractHandler {
        if ($handlerClass instanceof WhatsAppAbstractHandler) {
            return $handlerClass;
        }
        if (!$handlerClass || !class_exists($handlerClass)) {
            throw new \InvalidArgumentException(
                "Handler class '{$handlerClass}' does not exist"
            );
        }

        $handler = app($handlerClass);
        if (!($handler instanceof WhatsAppAbstractHandler)) {
            throw new \InvalidArgumentException(
                "Handler class '{$handlerClass}' must be an instance of WhatsAppTemplate, " .
                    get_class($handler) .
                    " given"
            );
        }

        return $handler;
    }
}
