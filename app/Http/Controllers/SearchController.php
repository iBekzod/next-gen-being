<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\SearchService;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Display the advanced search page
     */
    public function index(): View
    {
        return view('search.index');
    }

    /**
     * Get autocomplete suggestions (API endpoint)
     */
    public function suggestions(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = $this->searchService->getSuggestions($query, 10);

        return response()->json($suggestions);
    }

    /**
     * Get trending searches
     */
    public function trending()
    {
        $trends = $this->searchService->getTrendingSearches(10);

        return response()->json([
            'trending' => $trends->map(fn($tag) => [
                'label' => $tag->name,
                'count' => $tag->posts_count,
            ]),
        ]);
    }
}
