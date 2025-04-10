<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        // Get all available templates from the config
        $allTemplates = $this->getAllTemplateNames();

        // Create default descriptions for each template
        $descriptions = $this->getDefaultDescriptions();

        // Default reminder timings (minutes)
        $reminderTimings = $this->getDefaultReminderTimings();

        $this->migrator->add("notifications.enabled_templates", $allTemplates);
        $this->migrator->add(
            "notifications.reminder_timings",
            $reminderTimings
        );
        $this->migrator->add(
            "notifications.template_descriptions",
            $descriptions
        );
    }

    private function getAllTemplateNames(): array
    {
        $templateGroups = config("notification_templates", []);
        $templates = [];

        foreach ($templateGroups as $group => $groupTemplates) {
            foreach ($groupTemplates as $name => $class) {
                $templates[] = $name;
            }
        }

        return $templates;
    }

    private function getDefaultDescriptions(): array
    {
        //TODO: fix before deploy
        return [
            // Admin notifications
            "admin_booking_new" =>
                "Sent to admin when a new booking is created",
            "admin_booking_reminder" =>
                "Sent to admin before a booking pickup time",
            "admin_rent_new" => "Sent to admin when a new rental is created",
            "admin_rent_reminder" =>
                "Sent to admin before a rental start/end time",
            "admin_vehicle_inspection_reminder" =>
                "Sent to admin when a vehicle inspection is due",
            "admin_vehicle_registration_expiry_reminder" =>
                "Sent to admin when a vehicle registration is about to expire",
            "admin_shipping_new" =>
                "Sent to admin when a new shipping is created",
            "admin_shipping_reminder" =>
                "Sent to admin before a shipping pickup time",
            "admin_shipping_delivered" =>
                "Sent to admin when a shipping is delivered",

            // Driver notifications
            "driver_booking_new" =>
                "Sent to driver when assigned to a new booking",
            "driver_booking_reminder" =>
                "Sent to driver before a booking pickup time",
            "driver_booking_update" =>
                "Sent to driver when a booking is updated",
            "driver_vehicle_inspection_reminder" =>
                "Sent to driver when a vehicle inspection is due",
            "driver_shipping_confirmed" =>
                "Sent to driver when assigned to a new shipping",
            "driver_shipping_reminder" =>
                "Sent to driver before a shipping pickup time",
            "driver_shipping_delivery" =>
                "Sent to driver after pickup to confirm delivery",

            // Customer notifications
            "customer_booking_new" =>
                "Sent to customer when their booking is confirmed",
            "customer_rent_new" =>
                "Sent to customer when their rental is confirmed",
            "customer_rent_end_reminder" =>
                "Sent to customer before their rental ends",
            "customer_shipping_confirmed" =>
                "Sent to customer when their shipping is confirmed",
            "customer_shipping_picked_up" =>
                "Sent to customer when their package is picked up",
            "customer_shipping_delivered" =>
                "Sent to customer when their package is delivered",
        ];
    }

    private function getDefaultReminderTimings(): array
    {
        //TODO: fix before deploy
        return [
            // Default values in minutes
            "admin_booking_reminder" => 120, // 2 hours
            "admin_rent_reminder" => 120, // 2 hours
            "admin_vehicle_inspection_reminder" => 10080, // 7 days
            "admin_vehicle_registration_expiry_reminder" => 4320, // 3 days
            "admin_shipping_reminder" => 120, // 2 hours

            "driver_booking_reminder" => 30, // 30 minutes
            "driver_vehicle_inspection_reminder" => 4320, // 3 days
            "driver_shipping_reminder" => 30, // 30 minutes

            "customer_rent_end_reminder" => 1440, // 24 hours
        ];
    }
};
