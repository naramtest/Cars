<?php

namespace App\Console\Commands;

use App\Services\WhatsApp\WhatsAppNotificationService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Console\Command;

abstract class BaseNotificationCommand extends Command
{
    public function __construct(
        protected WhatsAppNotificationService $whatsAppService,
        protected WhatsAppTemplateService $whatsAppTemplateService
    ) {
        parent::__construct();
    }

    protected function sendNotification(
        $model,
        $handlerClass,
        $notificationType
    ): bool {
        if ($model->hasNotificationBeenSent($notificationType)) {
            $this->info("Already Sent"); // Already sent
            return false;
        }

        try {
            $this->whatsAppService->send($handlerClass, $model);

            // Record the notification
            $model->recordNotification($notificationType);

            $this->info(
                "Sent {$notificationType} for " .
                    class_basename($model) .
                    " #{$model->id}"
            );
            return true;
        } catch (\Exception $e) {
            $this->error(
                "Failed to send {$notificationType}: {$e->getMessage()}"
            );
            return false;
        }
    }
}
