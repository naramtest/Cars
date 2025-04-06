<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use App\Services\WhatsApp\Admin\Booking\ABReminderHandler;
use App\Services\WhatsApp\Driver\Booking\DBReminderHandler;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;

class SendBookingReminders extends BaseNotificationCommand
{
    protected $signature = "notifications:booking-reminders";
    protected $description = "Send WhatsApp reminders for upcoming bookings";

    public function handle()
    {
        $this->sendBookingReminder(app(DBReminderHandler::class));
        $this->sendBookingReminder(app(ABReminderHandler::class));
    }

    private function sendBookingReminder(WhatsAppAbstractHandler $handler): void
    {
        try {
            $template = $this->whatsAppTemplateService->resolveTemplate(
                $handler
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
                $this->sendNotification($booking, $handler, $template->name);
            }
        } catch (ConnectionException | \Exception $e) {
            $this->info($e->getMessage());
        }
    }
}
