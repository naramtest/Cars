<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Settings\NotificationSettings;
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
     */
    public function sendAndSave(
        string|WhatsAppAbstractHandler $handlerClass,
        $data,
        $recipients = null
    ): void {
        $handler = HandlerResolver::resolve($handlerClass);

        // Check if this notification type is enabled
        if (!$this->isNotificationEnabled($handler)) {
            throw new Exception("This notification is disabled in settings.");
        }

        //1- Check if template exists and return it
        $template = app(WhatsAppTemplateService::class)->resolveTemplate(
            $handler
        );
        //2- send template message
        $this->send($handler, $data, $recipients);
        //3- save sent message to the database
        $data->recordNotification($template->name);
    }

    /**
     * Check if the notification is enabled in settings
     */
    private function isNotificationEnabled(
        WhatsAppAbstractHandler $handler
    ): bool {
        try {
            /** @var NotificationSettings $settings */
            $settings = app(NotificationSettings::class);
            return $settings->isEnabled($handler->getTemplateName() ?? "");
        } catch (\Exception $e) {
            // Fallback to true if settings are not available yet
            return true;
        }
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
        if (!$this->isNotificationEnabled($whatsAppTemplate)) {
            throw new Exception("This notification is disabled in settings.");
        }

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
