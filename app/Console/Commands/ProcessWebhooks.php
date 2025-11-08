<?php

namespace App\Console\Commands;

use App\Services\Webhook\WebhookService;
use Illuminate\Console\Command;

class ProcessWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhooks:process
                            {--retry-failed : Retry failed webhooks}
                            {--cleanup : Clean up old logs}
                            {--days-old=30 : Number of days for cleanup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process webhook-related tasks (retry failed, cleanup logs)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $service = app(WebhookService::class);

        if ($this->option('retry-failed')) {
            $this->info('Retrying failed webhooks...');
            $service->retryFailedWebhooks();
            $this->info('Failed webhooks retry completed.');
        }

        if ($this->option('cleanup')) {
            $daysOld = (int) $this->option('days-old');
            $this->info("Cleaning up webhook logs older than {$daysOld} days...");
            $deleted = $service->cleanupOldLogs($daysOld);
            $this->info("Deleted {$deleted} webhook log entries.");
        }

        if (!$this->option('retry-failed') && !$this->option('cleanup')) {
            $this->info('No action specified. Use --retry-failed or --cleanup');
            return 0;
        }

        return 0;
    }
}
