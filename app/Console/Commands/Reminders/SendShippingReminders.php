<?php

namespace App\Console\Commands\Reminders;

use App\Console\Commands\BaseNotificationCommand;
use App\Enums\Shipping\ShippingStatus;
use App\Models\Shipping;
use App\Services\WhatsApp\Admin\Shipping\ASReminderHandler;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;

class SendShippingReminders extends BaseNotificationCommand
{
    protected $signature = "notifications:shipping-reminders";
    protected $description = "Send WhatsApp reminders for upcoming shipping pickups or deliveries";

    public function handle(AsReminderHandler $handler)
    {
        try {
            if (!$this->notificationEnabled($handler)) {
                return;
            }
            $minutesFromNow = Carbon::now()->addMinutes(
                $handler->getReminderTiming()
            );
            $template = $this->whatsAppTemplateService->resolveTemplate(
                $handler
            );

            // Look for shippings with received_at (pickup time) scheduled within the next 2 hours
            $upcomingShippings = Shipping::with([
                "driver",
                "items",
                "notifications",
            ])
                ->whereNotNull("pick_up_at")
                ->whereBetween("pick_up_at", [now(), $minutesFromNow])
                ->where("status", ShippingStatus::Pending->value)
                ->whereDoesntHave("notifications", function ($query) use (
                    $template
                ) {
                    $query->where("notification_type", $template->name);
                })
                ->get();

            $this->info(
                "Found {$upcomingShippings->count()} upcoming shippings to send reminders for"
            );

            foreach ($upcomingShippings as $shipping) {
                $this->sendNotification($shipping, $handler, $template->name);
            }

            return 0;
        } catch (ConnectionException | \Exception $e) {
            $this->error(
                "Error sending shipping reminders: " . $e->getMessage()
            );
            return 1;
        }
    }
}
