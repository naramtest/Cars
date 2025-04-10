<?php

namespace App\Console\Commands\Reminders;

use App\Console\Commands\BaseNotificationCommand;
use App\Models\Template;
use App\Models\Vehicle;
use App\Services\WhatsApp\Admin\Vehicle\VehicleRegistrationExpiryHandler;
use Illuminate\Http\Client\ConnectionException;

class SendVehicleRegistrationExpiryReminders extends BaseNotificationCommand
{
    protected $signature = "notifications:vehicle-registrations";
    protected $description = "Send WhatsApp reminders for vehicles with expiring registrations";

    /**
     * Execute the console command.
     */
    public function handle(VehicleRegistrationExpiryHandler $handler): int
    {
        try {
            // Check if the notification is enabled
            if (!$this->notificationEnabled($handler)) {
                return 0;
            }

            $template = $this->whatsAppTemplateService->resolveTemplate(
                $handler
            );

            // Get reminder timing from settings (convert minutes to days)
            $reminderMinutes = $handler->getReminderTiming();
            $daysBeforeExpiry = ceil($reminderMinutes / 1440); // Convert minutes to days

            $this->info(
                "Looking for vehicles with registrations expiring within $daysBeforeExpiry days"
            );

            // Find vehicles with registrations expiring soon
            $vehicles = Vehicle::with(["driver", "notifications"])
                ->whereDate("registration_expiry_date", ">", now())
                ->get()
                ->filter(function ($vehicle) use (
                    $template,
                    $daysBeforeExpiry
                ) {
                    // Check if we should send a notification for this day specifically
                    if (
                        now()->diffInDays($vehicle->registration_expiry_date) >
                        $daysBeforeExpiry
                    ) {
                        return false;
                    }

                    // Get notifications sent for this template with this specific expiry date and days
                    $existingNotification = $vehicle
                        ->notifications()
                        ->where(
                            "notification_type",
                            $this->getTemplateName($vehicle, $template)
                        )
                        ->exists();

                    // Only include vehicle if we haven't sent a notification for this specific
                    // expiry date and days remaining combination
                    return !$existingNotification;
                });

            $this->info(
                "Found " .
                    count($vehicles) .
                    " vehicles requiring registration expiry reminders"
            );

            foreach ($vehicles as $vehicle) {
                $this->sendNotification(
                    $vehicle,
                    $handler,
                    $this->getTemplateName($vehicle, $template)
                );
            }

            return 0;
        } catch (ConnectionException | \Exception $e) {
            $this->error(
                "Error sending registration expiry reminders: " .
                    $e->getMessage()
            );
            return 1;
        }
    }

    function getTemplateName(Vehicle $vehicle, Template $template): string
    {
        return $template->name .
            "_" .
            $vehicle->registration_expiry_date->format("Y-m-d");
    }
}
