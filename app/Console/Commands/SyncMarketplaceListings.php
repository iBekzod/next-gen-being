<?php

namespace App\Console\Commands;

use Database\Seeders\AscendListingSeeder;
use Database\Seeders\FitTrackListingSeeder;
use Database\Seeders\HaloListingSeeder;
use Database\Seeders\LandingPacksSeeder;
use Database\Seeders\LinkFolioListingSeeder;
use Database\Seeders\NebulaListingSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Barcha birinchi-tomon marketplace listinglarini ma'lumotlar bazasiga
 * yozadi.
 *
 * Mavjud bo'lish sababi: seeder'lar faqat testlardan chaqirilardi —
 * DatabaseSeeder ularni ro'yxatga olmagan, deploy.sh esa faqat
 * SiteSettingSeeder ni ishga tushirardi. Ya'ni har bir mahsulot
 * production'ga qo'lda chiqarilardi (spec §7).
 *
 * Barcha seeder'lar firstOrCreate/updateOrCreate ishlatadi, shuning uchun
 * bu buyruqni istalgancha qayta ishga tushirish xavfsiz.
 */
class SyncMarketplaceListings extends Command
{
    protected $signature = 'marketplace:sync-listings';
    protected $description = 'Barcha birinchi-tomon marketplace listinglarini idempotent tarzda yozadi';

    /** @var list<class-string> */
    private const SEEDERS = [
        FitTrackListingSeeder::class,
        LinkFolioListingSeeder::class,
        NebulaListingSeeder::class,
        HaloListingSeeder::class,
        AscendListingSeeder::class,
        LandingPacksSeeder::class,
    ];

    public function handle(): int
    {
        foreach (self::SEEDERS as $seeder) {
            $this->line('  ' . class_basename($seeder));
            (new $seeder())->run();
        }

        $count = \App\Models\MarketplaceListing::count();
        $this->info("✅ {$count} ta listing sinxronlandi.");
        Log::info('marketplace:sync-listings completed', ['listings' => $count]);

        return self::SUCCESS;
    }
}
