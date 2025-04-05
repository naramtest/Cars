<?php

namespace App\Services\WhatsApp\Abstract;

use Netflie\WhatsAppCloudApi\Message\Template\Component;

abstract class WhatsAppAbstractHandler implements WhatsAppNotificationInterface
{
    abstract public function facebookTemplateData(): array;

    public function getTemplateName(): ?string
    {
        $class = static::class;
        $group = $this->getGroup();
        $templates = config("notification_templates.$group");

        return array_search($class, $templates, true) ?: null;
    }

    abstract public function getGroup(): string;

    public function getComponent($data): Component
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
