<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    // Enabled notifications
    public array $enabled_templates;

    // Reminder timings (in minutes)
    public array $reminder_timings;

    // Descriptions (to be displayed on settings page)
    public array $template_descriptions;

    public static function group(): string
    {
        return "notifications";
    }

    public function isEnabled(string $templateName): bool
    {
        return in_array($templateName, $this->enabled_templates);
    }

    public function getReminderTiming(string $templateName): int
    {
        return $this->reminder_timings[$templateName] ?? 120; // Default to 2 hours
    }
}
