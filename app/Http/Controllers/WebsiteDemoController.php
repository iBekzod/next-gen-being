<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Services\DemoStorageService;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

/**
 * Serves marketplace demo files under the canonical path
 * /websites/{slug} (e.g. nextgenbeing.com/websites/nebula-analytics-saas).
 *
 * This replaces the old per-listing subdomain pattern
 * ({slug}-demo.nextgenbeing.com) which required per-site nginx vhosts.
 * Old subdomain URLs 301-redirect here via nginx (see jarvis/state/ngb-websites-nginx.md).
 *
 * Security: only published listings with a static demo are served.
 * The HTML file comes from Laravel's public disk (storage/app/public/demos/{slug}/index.html)
 * so it is already world-readable — this controller just enforces the listing
 * status check and sets the correct Content-Type.
 */
class WebsiteDemoController extends Controller
{
    public function show(string $slug, DemoStorageService $demos): Response|RedirectResponse
    {
        /** @var MarketplaceListing|null $listing */
        $listing = MarketplaceListing::published()
            ->where('slug', $slug)
            ->first();

        if (! $listing) {
            abort(404);
        }

        if ($listing->demo_type === 'external' && $listing->demo_url) {
            // External demos: redirect the visitor to the real URL.
            return redirect()->away($listing->demo_url, 302);
        }

        if ($listing->demo_type !== 'static' || ! $demos->exists($slug)) {
            abort(404);
        }

        $disk  = \Illuminate\Support\Facades\Storage::disk('public');
        $path  = "demos/{$slug}/index.html";
        $html  = $disk->get($path);

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=3600');
    }
}
