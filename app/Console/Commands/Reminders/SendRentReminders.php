<?php

namespace App\Console\Commands\Reminders;

use App\Console\Commands\BaseNotificationCommand;
use App\Models\Rent;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Services\WhatsApp\Admin\Rent\ARReminderHandler;
use App\Services\WhatsApp\Customer\Rent\CREndReminderHandler;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;

class SendRentReminders extends BaseNotificationCommand
{
    protected $signature = "notifications:rent-reminders";
    protected $description = "Send WhatsApp reminders for upcoming rent start and end events";

    public function handle()
    {
        //TODO: query and get Rents from the database only one time

        // Send start reminders
        $startHandler = new ARReminderHandler(ARReminderHandler::TYPE_START);
        if ($this->notificationEnabled($startHandler)) {
            $this->sendRentReminder($startHandler, "start");
        }

        // Send end reminders
        $endHandler = new ARReminderHandler(ARReminderHandler::TYPE_END);
        if ($this->notificationEnabled($endHandler)) {
            $this->sendRentReminder($endHandler, "end");
        }

        $endCustomerHandler = app(CREndReminderHandler::class);
        if ($this->notificationEnabled($endCustomerHandler)) {
            $this->sendRentReminder($endCustomerHandler, "end");
        }

        return 0;
    }

    private function sendRentReminder(
        WhatsAppAbstractHandler $handler,
        string $type
    ): void {
        try {
            $template = $this->whatsAppTemplateService->resolveTemplate(
                $handler
            );

            // Determine which date field to check based on reminder type
            $dateField =
                $type === "start" ? "rental_start_date" : "rental_end_date";

            // Find rents with events happening in about 2 hours
            $minutesFromNow = Carbon::now()->addMinutes(
                $handler->getReminderTiming()
            );

            $upcomingRents = Rent::with([
                "vehicle",
                "vehicle.driver",
                "notifications",
            ])
                ->whereBetween($dateField, [now(), $minutesFromNow])
                ->whereDoesntHave("notifications", function ($query) use (
                    $template
                ) {
                    $query->where("notification_type", $template->name);
                })
                ->get();

            $this->info(
                "Found {$upcomingRents->count()} upcoming rent {$type} events to send reminders for"
            );

            foreach ($upcomingRents as $rent) {
                $this->sendNotification($rent, $handler, $template->name);
            }
        } catch (ConnectionException | \Exception $e) {
            $this->error(
                "Error sending {$type} rent reminders: " . $e->getMessage()
            );
        }
    }
}
