<?php

namespace App\Listeners;

use App\Enums\Shipping\ShippingStatus;
use App\Events\ShippingCreated;
use App\Services\WhatsApp\Admin\Shipping\ASNewHandler;
use App\Services\WhatsApp\Customer\Shipping\CSNewHandler;
use App\Services\WhatsApp\Driver\Shipping\DSNewHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class SendShippingNotifications implements ShouldQueue
{
    public int $tries = 3;

    public function __construct(
        protected WhatsAppNotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ShippingCreated $event): void
    {
        $shipping = $event->shipping;
        try {
            // Always send admin notification
            $this->notificationService->sendAndSave(
                ASNewHandler::class,
                $shipping
            );

            if ($shipping->status === ShippingStatus::Confirmed) {
                if ($shipping->driver_id) {
                    $this->notificationService->sendAndSave(
                        DSNewHandler::class,
                        $shipping
                    );
                }
                $this->notificationService->sendAndSave(
                    CSNewHandler::class,
                    $shipping
                );
            }
        } catch (Exception $e) {
            Log::error("Failed to send booking notification", [
                "booking_id" => $shipping->id,
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
        }
    }
}
