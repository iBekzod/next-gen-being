<?php

namespace Database\Seeders\Concerns;

use App\Models\DigitalProduct;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Services\DemoStorageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait SeedsMarketplaceListing
{
    /**
     * Idempotently seed one first-party listing + its tiers, and install its
     * self-contained demo (read from database/seeders/demos/{slug}.html) onto
     * the public disk. Safe to run repeatedly.
     *
     * @param  array  $listing  attributes incl. 'slug' (required) and 'title'
     * @param  array  $tiers    list of ['tier','title','price','type','desc']
     */
    protected function seedMarketplaceListing(array $listing, array $tiers): ?MarketplaceListing
    {
        $seller = User::query()->orderBy('id')->first();
        if (! $seller) {
            return null; // needs a user; UserSeeder runs first
        }

        $slug = $listing['slug'];

        $model = MarketplaceListing::firstOrCreate(
            ['slug' => $slug],
            array_merge([
                'seller_id'         => $seller->id,
                'demo_type'         => 'static',
                'demo_path'         => "demos/{$slug}/index.html",
                'status'            => 'published',
                'plagiarism_status' => 'checked',
                'published_at'      => now(),
            ], $listing)
        );

        // Install the demo from the repo file (if present).
        $demoFile = database_path("seeders/demos/{$slug}.html");
        if (is_file($demoFile)) {
            app(DemoStorageService::class)->storeIndexHtml($slug, file_get_contents($demoFile));
        }

        foreach ($tiers as $t) {
            // Install the real deliverable (design = the demo HTML, prompt = the
            // build-plan doc). Tiers without a deliverable stay null and are shown
            // as "coming soon" / are not purchasable.
            $filePath = $this->installDeliverable($slug, $t['tier']);

            DigitalProduct::updateOrCreate(
                ['listing_id' => $model->id, 'tier' => $t['tier']],
                [
                    'creator_id'               => $seller->id,
                    'title'                    => $t['title'],
                    'slug'                     => Str::slug($t['title']),
                    'short_description'        => $t['desc'],
                    'description'              => $t['desc'],
                    'type'                     => $t['type'],
                    'price'                    => $t['price'],
                    'is_free'                  => $t['is_free'] ?? false,
                    'status'                   => 'published',
                    'published_at'             => now(),
                    'revenue_share_percentage' => DigitalProduct::MARKETPLACE_REVENUE_SHARE,
                    'file_path'                => $filePath,
                    // No lemonsqueezy_variant_id: marketplace tiers use the catch-all
                    // variant + custom_price at checkout.
                ]
            );
        }

        return $model;
    }

    /**
     * Copy a tier's source deliverable onto the private disk and return its path,
     * or null when there is no deliverable for this tier yet.
     */
    protected function installDeliverable(string $slug, string $tier): ?string
    {
        $sources = [
            'design' => [database_path("seeders/demos/{$slug}.html"), 'html'],
            'prompt' => [database_path("seeders/deliverables/{$slug}-prompt.md"), 'md'],
        ];

        if (! isset($sources[$tier])) {
            return null; // code / bundle: no deliverable yet
        }

        [$source, $ext] = $sources[$tier];
        if (! is_file($source)) {
            return null;
        }

        $dest = "deliverables/{$slug}/{$tier}.{$ext}";
        Storage::disk('private')->put($dest, file_get_contents($source));

        return $dest;
    }
}
