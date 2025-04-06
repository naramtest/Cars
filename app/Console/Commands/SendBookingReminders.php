<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\WhatsApp\Driver\Booking\DBReminderHandler;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;

class SendBookingReminders extends BaseNotificationCommand
{
    protected $signature = "notifications:booking-reminders";
    protected $description = "Send WhatsApp reminders for upcoming bookings";

    public function handle()
    {
        // Find bookings that need driver notifications (2 hours before)
        $this->sendDriverReminders();

        // Find bookings that need admin notifications (1 day before)
        $this->sendAdminReminders();
    }

    private function sendDriverReminders(): void
    {
        try {
            $dbReminderHandler = app(DBReminderHandler::class);
            $template = $this->whatsAppTemplateService->resolveTemplate(
                $dbReminderHandler
            );

            // Find bookings starting in about 2 hours
            $twoHoursFromNow = Carbon::now()->addHours(2);

            $upcomingBookings = Booking::with(["driver", "notifications"])
                ->whereBetween("start_datetime", [now(), $twoHoursFromNow])
                ->whereDoesntHave("notifications", function ($query) use (
                    $template
                ) {
                    $query->where("notification_type", $template->name);
                })
                ->get();
            
            foreach ($upcomingBookings as $booking) {
                $this->sendNotification(
                    $booking,
                    $dbReminderHandler,
                    $template->name
                );
            }
        } catch (ConnectionException | \Exception $e) {
            $this->info($e->getMessage());
        }
    }

    private function sendAdminReminders()
    {
        // Similar logic for admin reminders (1 day before)
        // ...
    }
}
