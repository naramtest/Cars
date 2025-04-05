<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use Exception;
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
     * @throws Exception
     */
    public function send(
        string|WhatsAppAbstractHandler $handlerClass,
        $data,
        $recipients = null
    ): array {
        $whatsAppTemplate = HandlerResolver::resolve($handlerClass);

        // Check if this notification type is enabled
        if (!$whatsAppTemplate->isEnabled()) {
            throw new Exception("This Notification Template is not enabled.");
        }
        $recipients ??= $whatsAppTemplate->phoneNumbers($data);

        return $this->sendTemplateMessage(
            $recipients,
            $whatsAppTemplate->getTemplateName(),
            $whatsAppTemplate->getComponent($data)
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
                $recipient[] = $this->whatsAppClient->sendTemplate(
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
