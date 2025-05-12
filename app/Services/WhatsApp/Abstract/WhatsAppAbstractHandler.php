<?php

namespace App\Services\WhatsApp\Abstract;

use App\Settings\NotificationSettings;
use Netflie\WhatsAppCloudApi\Message\Template\Component;

abstract class WhatsAppAbstractHandler implements WhatsAppNotificationInterface
{
    abstract public function facebookTemplateData(): array;

    public function getComponent($data): Component
    {
        $header = [];
        $body = $this->prepareBodyData($data);
        $buttons = $this->prepareButtonData($data);

        return new Component($header, $body, $buttons);
    }

    abstract public function prepareBodyData($modelData): array;

    abstract public function prepareButtonData($modelData): array;

    public function isEnabled(): bool
    {
        try {
            /** @var NotificationSettings $settings */
            $settings = app(NotificationSettings::class);
            return $settings->isEnabled($this->getTemplateName() ?? "");
        } catch (\Exception) {
            // Fallback to true if settings are not available yet
            return true;
        }
    }

    public function getTemplateName(): ?string
    {
        $class = static::class;
        $group = $this->getGroup();
        $templates = config("notification_templates.$group");

        return array_search($class, $templates, true) ?: null;
    }

    abstract public function getGroup(): string;

    public function getReminderTiming(): int
    {
        try {
            /** @var NotificationSettings $settings */
            $settings = app(NotificationSettings::class);
            return $settings->getReminderTiming($this->getTemplateName() ?? "");
        } catch (\Exception $e) {
            // Fallback to 120 minutes (2 hours) if settings are not available
            return 120;
        }
    }

    public function getDescription(): string
    {
        try {
            /** @var NotificationSettings $settings */
            $settings = app(NotificationSettings::class);
            $descriptions = $settings->template_descriptions;
            return $descriptions[$this->getTemplateName()] ??
                "No description available";
        } catch (\Exception $e) {
            return "No description available";
        }
    }

    protected function formatBodyParameters($parameters): array
    {
        $components_body = [];
        foreach ($parameters as $value) {
            $components_body[] = [
                "type" => "text",
                "text" => $this->sanitizeText((string) $value),
            ];
        }

        return $components_body;
    }

    /**
     * Sanitize text for WhatsApp API (remove newlines, tabs, and excessive spaces)
     *
     * @param string|null $text
     * @return string
     */
    protected function sanitizeText(?string $text): string
    {
        if (empty($text)) {
            return "";
        }
        // Replace newlines and tabs with a single space
        $text = preg_replace('/[\r\n\t]+/', " ", $text);
        // Replace multiple spaces with a single space
        $text = preg_replace("/\s{2,}/", " ", $text);
        // Trim the text
        return trim($text);
    }
}
