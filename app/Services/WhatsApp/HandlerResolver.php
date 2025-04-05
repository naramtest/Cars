<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Abstract\WhatsAppTemplate;

class HandlerResolver
{
    public static function resolve(
        string|WhatsAppTemplate $handlerClass
    ): WhatsAppTemplate {
        if ($handlerClass instanceof WhatsAppTemplate) {
            return $handlerClass;
        }
        if (!$handlerClass || !class_exists($handlerClass)) {
            throw new \InvalidArgumentException(
                "Handler class '{$handlerClass}' does not exist"
            );
        }

        $handler = app($handlerClass);
        if (!($handler instanceof WhatsAppTemplate)) {
            throw new \InvalidArgumentException(
                "Handler class '{$handlerClass}' must be an instance of WhatsAppTemplate, " .
                    get_class($handler) .
                    " given"
            );
        }

        return $handler;
    }
}
