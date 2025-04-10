<?php

namespace App\Console\Commands\Reminders\Driver;

use App\Console\Commands\BaseNotificationCommand;
use App\Enums\Shipping\ShippingStatus;
use App\Models\Shipping;
use App\Services\WhatsApp\Driver\Shipping\DSReminderHandler;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;

class SendShippingDriverReminders extends BaseNotificationCommand
{
    protected $signature = "notifications:shipping-driver-reminders";
    protected $description = "Send WhatsApp reminders to drivers 30 minutes before pickup time";

    public function handle(DSReminderHandler $handler)
    {
        try {
            $template = $this->whatsAppTemplateService->resolveTemplate(
                $handler
            );

            // Find shippings scheduled for pickup in ~30 minutes
            $windowEnd = Carbon::now()->addMinutes(35);

            $upcomingShippings = Shipping::with(["driver", "notifications"])
                ->whereNotNull("pick_up_at")
                ->whereNotNull("driver_id")
                ->whereBetween("pick_up_at", [now(), $windowEnd])
                ->where("status", ShippingStatus::Confirmed)
                ->whereDoesntHave("notifications", function ($query) use (
                    $template
                ) {
                    $query->where("notification_type", $template->name);
                })
                ->get();

            $this->info(
                "Found {$upcomingShippings->count()} upcoming shippings for driver reminders"
            );

            foreach ($upcomingShippings as $shipping) {
                $this->sendNotification($shipping, $handler, $template->name);
            }

            return 0;
        } catch (ConnectionException | \Exception $e) {
            $this->error(
                "Error sending shipping driver reminders: " . $e->getMessage()
            );
            return 1;
        }
    }
}
