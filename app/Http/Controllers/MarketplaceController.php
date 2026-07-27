<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Services\DemoStorageService;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceListing::published()->withCount('tiers')->with('tiers');

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $sort = $request->query('sort', 'newest');
        match ($sort) {
            'popular' => $query->orderByDesc('sales_count'),
            default   => $query->orderByDesc('published_at'),
        };

        $listings = $query->paginate(12)->withQueryString();

        $categories = MarketplaceListing::published()
            ->whereNotNull('category')->distinct()->pluck('category');

        return view('marketplace.index', compact('listings', 'categories'));
    }

    public function show(MarketplaceListing $listing, DemoStorageService $demos)
    {
        abort_unless($listing->status === 'published', 404);

        $listing->load(['tiers', 'seller']);

        $demoUrl = null;
        if ($listing->demo_type === 'static' && $demos->exists($listing->slug)) {
            $demoUrl = $demos->url($listing->slug);
        } elseif ($listing->demo_type === 'external') {
            $demoUrl = $listing->demo_url;
        }

        $related = MarketplaceListing::published()
            ->where('id', '!=', $listing->id)
            ->where('category', $listing->category)
            ->with('tiers')->take(4)->get();

        return view('marketplace.show', compact('listing', 'demoUrl', 'related'));
    }
}
