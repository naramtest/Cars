<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsAppNotificationService
{
    protected WhatsAppCloudApi $whatsAppClient;
    protected WhatsAppAbstractHandler $template;

    public function __construct(WhatsAppCloudApi $whatsAppClient)
    {
        $this->whatsAppClient = $whatsAppClient;
    }

    /**
     * @throws ResponseException
     * @throws ConnectionException
     * @throws Exception
     */
    public function sendAndSave(
        string|WhatsAppAbstractHandler $handlerClass,
        $data,
        $recipients = null
    ): void {
        $handler = HandlerResolver::resolve($handlerClass);

        // Check if this notification type is enabled
        if (!$handler->isEnabled()) {
            throw new Exception(
                __("notification.This notification is disabled in settings")
            );
        }

        //1- Check if template exists and return it
        $template = app(WhatsAppTemplateService::class)->resolveTemplate(
            $handler
        );
        if ($data->hasNotificationBeenSent($template->name)) {
            throw new Exception("Notification already sent.");
        }
        //2- send template message
        $this->send($handler, $data, $recipients);
        //3- save sent message to the database
        $data->recordNotification($template->name);
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function send(
        string|WhatsAppAbstractHandler $handlerClass,
        $data,
        $recipients = null
    ): array {
        $handler = HandlerResolver::resolve($handlerClass);

        // Check if this notification type is enabled
        if (!$handler->isEnabled()) {
            throw new Exception(
                __("notification.This notification is disabled in settings")
            );
        }
        $recipients ??= $handler->phoneNumbers($data);

        return $this->sendTemplateMessage(
            $recipients,
            $handler->getTemplateName(),
            $handler->getComponent($data)
        );
    }

    /**
     * @param array|string $recipients
     * @param string|null $templateId
     * @param Component $component
     * @return array
     * @throws ResponseException
     */
    public function sendTemplateMessage(
        array|string $recipients,
        ?string $templateId,
        Component $component
    ): array {
        $responses = [];
        if (is_array($recipients)) {
            foreach ($recipients as $recipient) {
                $responses[] = $this->whatsAppClient->sendTemplate(
                    $recipient,
                    $templateId,
                    components: $component
                );
            }
            return $responses;
        }
        $responses[] = $this->whatsAppClient->sendTemplate(
            $recipients,
            $templateId,
            components: $component
        );
        return $responses;
    }
}
