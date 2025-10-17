<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsletterService;

class SendWeeklyNewsletter extends Command
{
    protected $signature = 'newsletter:send-weekly';
    protected $description = 'Send weekly newsletter to all weekly subscribers';

    public function handle(NewsletterService $newsletterService)
    {
        $this->info('🚀 Generating weekly newsletter campaign...');

        $campaign = $newsletterService->generateWeeklyDigest();

        $this->info('📧 Sending to subscribers...');

        $sentCount = $newsletterService->sendCampaign($campaign, 'weekly');

        $this->info("✅ Newsletter sent to {$sentCount} subscribers!");

        return Command::SUCCESS;
    }
}
