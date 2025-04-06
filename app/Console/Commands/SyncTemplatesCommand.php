<?php

namespace App\Console\Commands;

use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Console\Command;

class SyncTemplatesCommand extends Command
{
    protected $signature = "whatsapp:sync-templates";
    protected $description = "Sync all WhatsApp notification templates with Meta";

    public function __construct(
        protected WhatsAppTemplateService $templateService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Starting WhatsApp template synchronization...");

        $templateGroups = config("notification_templates");
        $successCount = 0;
        $failureCount = 0;

        foreach ($templateGroups as $group => $templates) {
            $this->info("Processing template group: $group");

            foreach ($templates as $templateName => $handlerClass) {
                try {
                    $this->templateService->resolveTemplate($handlerClass);
                    $this->info(
                        "✓ Template synced successfully: $templateName"
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error(
                        "✗ Failed to sync template $templateName: " .
                            $e->getMessage()
                    );
                    $failureCount++;
                }
            }
        }

        $this->newLine();
        $this->info(
            "Synchronization completed: $successCount templates synced, $failureCount failures"
        );

        return 0;
    }
}
