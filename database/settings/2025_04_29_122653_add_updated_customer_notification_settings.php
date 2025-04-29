<?php

use App\Settings\NotificationSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $settings = app(NotificationSettings::class);
        // Get current enabled templates
        $enabledTemplates = $settings->enabled_templates;

        // Add new templates to the list
        $newTemplates = [
            "customer_booking_update",
            "customer_rent_update",
            "customer_shipping_update",
        ];

        // Check if templates are already in the list before adding
        foreach ($newTemplates as $template) {
            if (!in_array($template, $enabledTemplates)) {
                $enabledTemplates[] = $template;
            }
        }
        logger($enabledTemplates);
        // Update enabled templates
        $this->migrator->update(
            "notifications.enabled_templates",
            fn($value) => $enabledTemplates
        );

        // Get current template descriptions
        $templateDescriptions = $settings->template_descriptions;

        // Add new template descriptions
        $newDescriptions = [
            "customer_booking_update" =>
                "Sent to customer when their booking details are updated",
            "customer_rent_update" =>
                "Sent to customer when their rental details are updated",
            "customer_shipping_update" =>
                "Sent to customer when their shipping details are updated",
        ];

        // Merge new descriptions with existing ones
        $templateDescriptions = array_merge(
            $templateDescriptions,
            $newDescriptions
        );

        // Update template descriptions
        $this->migrator->update(
            "notifications.template_descriptions",
            fn($value) => $templateDescriptions
        );
    }
};
