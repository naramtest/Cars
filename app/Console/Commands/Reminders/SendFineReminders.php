<?php

namespace App\Console\Commands\Reminders;

use App\Console\Commands\BaseNotificationCommand;
use App\Models\Fine;
use App\Services\WhatsApp\Customer\Rent\CRFineReminderHandler;
use Illuminate\Http\Client\ConnectionException;

class SendFineReminders extends BaseNotificationCommand
{
    protected $signature = 'notifications:fine-reminders';
    protected $description = 'Send daily WhatsApp reminders for unpaid traffic fines';

    public function handle()
    {
        $this->sendFineReminders();
        return 0;
    }

    private function sendFineReminders(): void
    {
        try {
            $handler = app(CRFineReminderHandler::class);

            if (!$this->notificationEnabled($handler)) {
                $this->info('Fine notifications are disabled');
                return;
            }

            $template = $this->whatsAppTemplateService->resolveTemplate($handler);

            $pendingFines = Fine::with(['rent.customer', 'rent.notifications'])
                ->needsNotification()
                ->get();

            $finesByCustomer = $pendingFines->groupBy(function ($fine) {
                return $fine->rent->getCustomer()->id;
            });

            $this->info("Found {$finesByCustomer->count()} customers with pending traffic fines");

            foreach ($finesByCustomer as $customerId => $customerFines) {
                $rent = $customerFines->first()->rent;

                try {
                    $result = $this->sendNotification($rent, $handler, $template->name . "-" . now()->timestamp);

                    if ($result) {
                        $customerFines->each(function ($fine) {
                            $fine->updateLastNotificationSent();
                        });
                    }

                    $this->info("Sent fine reminder to customer ID: {$customerId}");

                } catch (\Exception $e) {
                    $this->error("Failed to send fine reminder to customer ID {$customerId}: " . $e->getMessage());
                }
            }

        } catch (ConnectionException|\Exception $e) {
            $this->error('Error sending fine reminders: ' . $e->getMessage());
        }
    }
}
