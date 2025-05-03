<?php

namespace App\Observers;

use App\Services\WhatsApp\WhatsAppNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Netflie\WhatsAppCloudApi\Response\ResponseException;

class NotificationObserver
{
    public function __construct(
        protected WhatsAppNotificationService $notificationService
    ) {}

    //TODO:resend booking updated  notification even when it sends
    protected function sendAndSave(
        string $class,
        Model $model,
        bool $isUpdate = false
    ): void {
        try {
            $this->notificationService->sendAndSave(
                $class,
                $model,
                isUpdate: $isUpdate
            );
        } catch (ConnectionException | ResponseException | \Exception $e) {
            logger()->error(
                "Failed to send  notification: " . $e->getMessage(),
                [
                    "model_id" => $model->id,
                    "handler" => $class,
                ]
            );
        }
    }
}
