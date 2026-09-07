<?php

namespace App\Console\Commands;

use App\Services\NewsletterService;
use Illuminate\Console\Command;

class SendDailyNewsletter extends Command
{
    protected $signature = 'newsletter:send-daily';
    protected $description = 'Kunlik digestni daily obunachilarga yuboradi';

    public function __construct(private readonly NewsletterService $newsletter)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $campaign = $this->newsletter->generateDailyDigest();

        if ($campaign === null) {
            $this->info('Bugun yuboriladigan yangilik yo\'q — digest o\'tkazib yuborildi.');

            return self::SUCCESS;
        }

        $sent = $this->newsletter->sendCampaign($campaign, 'daily');
        $this->info("✅ Kunlik digest {$sent} ta obunachiga yuborildi.");

        return self::SUCCESS;
    }
}
