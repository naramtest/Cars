<?php

namespace App\Services\WhatsApp;

use Exception;
use Log;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Response;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

abstract class AbstractNotificationHandler
{
    public function __construct(public WhatsAppCloudApi $whatsAppClient) {}

    /**
     *
     * @throws Exception
     */
    public function send(
        array $data,
        array|string $phone_numbers = null
    ): Response|array {
        $templateId = $this->getTemplateId();
        $messageData = $this->prepareData($data);

        // Process recipient (could be an array or single number)
        if (is_array($phone_numbers)) {
            return $this->sendBatch($templateId, $messageData, $phone_numbers);
        }

        return $this->sendSingle($templateId, $messageData, $phone_numbers);
    }

    public function getTemplateId(): ?string
    {
        $class = static::class;
        $group = $this->getGroup();
        $templates = config("notification_templates.$group");

        return array_search($class, $templates, true) ?: null;
    }

    abstract protected function getGroup(): string;

    abstract public function prepareData(array $modelData): array;

    protected function sendBatch(
        string $templateId,
        array $messageData,
        array $recipients
    ): array {
        $results = [];

        foreach ($recipients as $recipient) {
            try {
                $results[$recipient] = $this->sendSingle(
                    $templateId,
                    $messageData,
                    $recipient
                );
            } catch (Exception $e) {
                $results[$recipient] = ["error" => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * @throws ResponseException
     */
    protected function sendSingle(
        string $templateId,
        array $messageData,
        string $recipient
    ): Response {
        try {
            return $this->whatsAppClient->sendTemplate(
                $recipient,
                $templateId,
                components: $this->formatBodyParameters($messageData)
            );
        } catch (Exception $e) {
            Log::error("WhatsApp notification failed", [
                "template" => $templateId,
                "recipient" => $recipient,
                "error" => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function formatBodyParameters($parameters): Component
    {
        // Structure will depend on netflie/whatsapp-cloud-api requirements
        $components_body = [];

        foreach ($parameters as $value) {
            $components_body[] = [
                "type" => "text",
                "text" => (string) $value,
            ];
        }

        return new Component([], $components_body, [
            //here we will add the button data
        ]);
    }
}
