<?php

namespace App\Console\Commands\Reminders;

use App\Console\Commands\BaseNotificationCommand;
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
    public function handle()
    {
        try {
            $handler = app(VehicleRegistrationExpiryHandler::class);
            $template = $this->whatsAppTemplateService->resolveTemplate(
                $handler
            );

            // Number of days before expiry to send first notification
            $reminderDays = config("vehicle.registration_reminder_days", [
                30,
                14,
                7,
                3,
                1,
            ]);

            // Find vehicles with registrations expiring soon
            $vehicles = Vehicle::with(["driver", "notifications"])
                ->whereDate("registration_expiry_date", ">", now())
                ->whereDate(
                    "registration_expiry_date",
                    "<=",
                    now()->addDays(max($reminderDays))
                )
                ->get()
                ->filter(function ($vehicle) use ($reminderDays, $template) {
                    // Calculate days until expiry
                    $daysUntilExpiry = now()->diffInDays(
                        $vehicle->registration_expiry_date,
                        false
                    );

                    // Check if we should send a notification for this number of days
                    if (!in_array($daysUntilExpiry, $reminderDays)) {
                        return false;
                    }

                    // Create a key that includes the actual expiry date and days remaining
                    // This ensures that after a renewal, we'll generate new notifications
                    $expiryDate = $vehicle->registration_expiry_date->format(
                        "Y-m-d"
                    );
                    $notificationKey =
                        $template->name .
                        "_" .
                        $expiryDate .
                        "_" .
                        $daysUntilExpiry;

                    // Get notifications sent for this template with this specific expiry date and days
                    $existingNotification = $vehicle
                        ->notifications()
                        ->where("notification_type", $notificationKey)
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
                $daysUntilExpiry = now()->diffInDays(
                    $vehicle->registration_expiry_date,
                    false
                );
                $expiryDate = $vehicle->registration_expiry_date->format(
                    "Y-m-d"
                );
                $notificationKey =
                    $template->name .
                    "_" .
                    $expiryDate .
                    "_" .
                    $daysUntilExpiry;

                // Send notification with the day-specific key
                $this->sendNotification($vehicle, $handler, $notificationKey);
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
}
