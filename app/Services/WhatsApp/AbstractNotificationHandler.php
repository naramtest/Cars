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
    public function send($data, $phone_numbers = null): Response|array
    {
        $templateId = $this->getTemplateId();
        $phone_numbers ??= $this->setPhoneNumbers($data);

        // Process recipient (could be an array or single number)
        if (is_array($phone_numbers)) {
            return $this->sendBatch($templateId, $data, $phone_numbers);
        }

        return $this->sendSingle($templateId, $data, $phone_numbers);
    }

    public function getTemplateId(): ?string
    {
        $class = static::class;
        $group = $this->getGroup();
        $templates = config("notification_templates.$group");

        return array_search($class, $templates, true) ?: null;
    }

    abstract protected function getGroup(): string;

    abstract protected function setPhoneNumbers($data);

    protected function sendBatch(
        string $templateId,
        $messageData,
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
        $messageData,
        string $recipient
    ): Response {
        try {
            return $this->whatsAppClient->sendTemplate(
                $recipient,
                $templateId,
                components: $this->getComponent($messageData)
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

    protected function getComponent($data): Component
    {
        $header = [];
        $body = $this->prepareBodyData($data);
        $buttons = $this->prepareButtonData($data);

        return new Component($header, $body, $buttons);
    }

    abstract public function prepareBodyData($modelData): array;

    abstract public function prepareButtonData($modelData): array;

    protected function formatBodyParameters($parameters): array
    {
        $components_body = [];
        foreach ($parameters as $value) {
            $components_body[] = [
                "type" => "text",
                "text" => (string) $value,
            ];
        }

        return $components_body;
    }
}
