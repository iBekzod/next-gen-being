<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NewsletterSubscription;
use App\Models\NewsletterEngagement;
use App\Models\NewsletterCampaign;

class CleanupNewsletterData extends Command
{
    protected $signature = 'newsletter:cleanup';
    protected $description = 'Cleanup old newsletter data';

    public function handle()
    {
        $this->info('🧹 Cleaning up old newsletter data...');

        $deletedEngagements = NewsletterEngagement::where('created_at', '<', now()->subMonths(6))->delete();
        $this->info("   Deleted {$deletedEngagements} old engagement records");

        $deletedCampaigns = NewsletterCampaign::where('status', 'sent')
            ->where('sent_at', '<', now()->subYear())
            ->delete();
        $this->info("   Deleted {$deletedCampaigns} old campaigns");

        $deletedUnverified = NewsletterSubscription::whereNull('verified_at')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
        $this->info("   Deleted {$deletedUnverified} unverified subscriptions");

        $this->info('✅ Cleanup complete!');

        return Command::SUCCESS;
    }
}
