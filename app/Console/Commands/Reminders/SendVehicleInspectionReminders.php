<?php

namespace App\Console\Commands\Reminders;

use App\Console\Commands\BaseNotificationCommand;
use App\Models\Vehicle;
use App\Services\WhatsApp\Admin\Vehicle\VehicleInspectionReminderHandler;
use Exception;
use Illuminate\Http\Client\ConnectionException;

class SendVehicleInspectionReminders extends BaseNotificationCommand
{
    protected $signature = "notifications:vehicle-inspections";
    protected $description = "Send WhatsApp reminders for vehicles due for inspection";

    public function handle(VehicleInspectionReminderHandler $handler)
    {
        try {
            if (!$this->notificationEnabled($handler)) {
                return 0;
            }

            $template = $this->whatsAppTemplateService->resolveTemplate(
                $handler
            );
            $notificationDays = ceil($handler->getReminderTiming() / 1440);

            // Find vehicles that need inspection reminders
            $vehicles = Vehicle::with([
                "driver",
                "inspections",
                "notifications",
            ])
                ->whereNotNull("next_inspection_date")
                ->where("notify_before_inspection", true)
                ->get()
                ->filter(function (Vehicle $vehicle) use ($notificationDays) {
                    // Check if due for inspection

                    return !$vehicle->next_inspection_date->isSameDay(
                        $vehicle->created_at
                    ) &&
                        $vehicle->days_until_next_inspection <=
                            $notificationDays &&
                        $vehicle->days_until_next_inspection >= 0;
                })
                ->filter(function ($vehicle) use ($template) {
                    // Get the latest inspection date or vehicle creation date
                    $lastInspection = $vehicle
                        ->inspections()
                        ->latest()
                        ->first();
                    $lastInspectionDate = $lastInspection
                        ? $lastInspection->inspection_date
                        : $vehicle->created_at;

                    // Check if notification has already been sent since last inspection
                    $notificationAfterLastInspection = $vehicle
                        ->notifications()
                        ->where("notification_type", $template->name)
                        ->where("sent_at", ">", $lastInspectionDate)
                        ->exists();

                    // Only send a notification if none has been sent since the last inspection
                    return !$notificationAfterLastInspection;
                });

            $this->info(
                "Found " .
                    count($vehicles) .
                    " vehicles requiring inspection reminders"
            );

            foreach ($vehicles as $vehicle) {
                $this->sendNotification($vehicle, $handler, $template->name);
            }

            return 0;
        } catch (ConnectionException | Exception $e) {
            $this->error(
                "Error sending inspection reminders: " . $e->getMessage()
            );
            return 1;
        }
    }
}
