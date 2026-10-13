<?php

namespace App\Console\Commands;

use App\Models\MarketplaceListing;
use Illuminate\Console\Command;

/**
 * Sets every listing's sales_count / reviews_count / rating from real data
 * (completed paid purchases; no reviews exist yet => 0).
 */
class RecomputeMarketplaceStats extends Command
{
    protected $signature = 'marketplace:recompute-stats';
    protected $description = 'Recompute listing sales/rating from real purchases (removes any fake numbers)';

    public function handle(): int
    {
        MarketplaceListing::withTrashed()->each(function (MarketplaceListing $l) {
            $before = "{$l->rating}/{$l->reviews_count}/{$l->sales_count}";
            $l->refreshStats();
            $this->line("{$l->slug}: {$before} -> {$l->rating}/{$l->reviews_count}/{$l->sales_count}");
        });

        return self::SUCCESS;
    }
}
