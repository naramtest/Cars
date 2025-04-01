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

    abstract public function getTemplateId();

    abstract public function prepareData(array $modelData);

    protected function sendBatch(
        $templateId,
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
        $templateId,
        $messageData,
        $recipient
    ): Response {
        try {
            return $this->whatsAppClient->sendTemplate(
                $recipient,
                $templateId,
                components: $this->formatParametersForWhatsApp($messageData)
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

    protected function formatParametersForWhatsApp($parameters): Component
    {
        // Structure will depend on netflie/whatsapp-cloud-api requirements
        $components_body = [];

        foreach ($parameters as $key => $value) {
            $components_body[] = [
                "type" => "text",
                "text" => (string) $value,
            ];
        }

        return new Component([], $components_body, []);
    }
}
