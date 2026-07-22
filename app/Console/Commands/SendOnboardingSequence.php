<?php

namespace App\Console\Commands;

use App\Services\NewsletterService;
use Illuminate\Console\Command;

class SendOnboardingSequence extends Command
{
    protected $signature = 'newsletter:send-onboarding';

    protected $description = 'Send the next due welcome/onboarding email to confirmed subscribers';

    public function handle(NewsletterService $newsletterService): int
    {
        $count = $newsletterService->processOnboardingQueue();

        $this->info("Onboarding emails sent: {$count}");

        return self::SUCCESS;
    }
}
