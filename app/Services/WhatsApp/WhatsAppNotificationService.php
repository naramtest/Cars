<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Abstract\WhatsAppTemplate;
use Exception;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsAppNotificationService
{
    protected WhatsAppCloudApi $whatsAppClient;

    public function __construct(WhatsAppCloudApi $whatsAppClient)
    {
        $this->whatsAppClient = $whatsAppClient;
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function send(
        string $notificationClass,
        $data,
        $recipients = null,
        WhatsAppTemplate $whatsAppTemplate = null
    ): array {
        // Check if this notification type is enabled
        if (!$whatsAppTemplate->isEnabled($notificationClass)) {
            throw new Exception("This Notification Template is not enabled.");
        }
        $recipients ??= $whatsAppTemplate->phoneNumbers($data);

        return $this->sendTemplateMessage(
            $recipients,
            $whatsAppTemplate->getTemplateId(),
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
        }
        $responses[] = $this->whatsAppClient->sendTemplate(
            $recipients,
            $templateId,
            components: $component
        );
        return $responses;
    }
}
