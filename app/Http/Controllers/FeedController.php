<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeedController extends Controller
{
    /**
     * Display personalized feed for authenticated users
     * Shows posts from bloggers the user follows
     */
    public function index(Request $request): View
    {
        if (!Auth::check()) {
            return view('feed.guest');
        }

        $user = Auth::user();

        // Get IDs of bloggers the user follows
        $followingIds = $user->following()->pluck('users.id');

        if ($followingIds->isEmpty()) {
            // User doesn't follow anyone yet
            return view('feed.empty');
        }

        // Build query for posts from followed bloggers
        $query = Post::whereIn('author_id', $followingIds)
            ->where('status', 'published')
            ->where('moderation_status', 'approved')
            ->with(['author', 'category', 'tags']);

        // Filter by content type
        if ($request->filled('type')) {
            switch ($request->type) {
                case 'premium':
                    $query->where('is_premium', true);
                    break;
                case 'free':
                    $query->where('is_premium', false);
                    break;
                case 'tutorial':
                    $query->whereNotNull('series_slug');
                    break;
            }
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Sort options
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderByDesc('views_count')
                      ->orderByDesc('published_at');
                break;
            case 'trending':
                // Posts with high engagement in last 7 days
                $query->where('published_at', '>=', now()->subDays(7))
                      ->orderByDesc('views_count')
                      ->orderByDesc('published_at');
                break;
            default: // latest
                $query->latest('published_at');
        }

        $posts = $query->paginate(15);

        // Get categories for filter
        $categories = \App\Models\Category::whereHas('posts', function ($q) use ($followingIds) {
            $q->whereIn('author_id', $followingIds)
              ->where('status', 'published');
        })->get();

        return view('feed.index', [
            'posts' => $posts,
            'categories' => $categories,
            'currentSort' => $sort,
            'currentType' => $request->type,
            'currentCategory' => $request->category,
            'followingCount' => $followingIds->count(),
        ]);
    }

    /**
     * Display global feed for everyone (homepage)
     */
    public function global(Request $request): View
    {
        $query = Post::where('status', 'published')
            ->where('moderation_status', 'approved')
            ->with(['author', 'category', 'tags']);

        // Filter by content type
        if ($request->filled('type')) {
            switch ($request->type) {
                case 'premium':
                    $query->where('is_premium', true);
                    break;
                case 'free':
                    $query->where('is_premium', false);
                    break;
                case 'tutorial':
                    $query->whereNotNull('series_slug');
                    break;
            }
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        // Sort options
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderByDesc('views_count')
                      ->orderByDesc('published_at');
                break;
            case 'trending':
                $query->where('published_at', '>=', now()->subDays(7))
                      ->orderByDesc('views_count')
                      ->orderByDesc('published_at');
                break;
            default: // latest
                $query->latest('published_at');
        }

        $posts = $query->paginate(15);

        // Get all categories
        $categories = \App\Models\Category::whereHas('posts', function ($q) {
            $q->where('status', 'published');
        })->get();

        return view('feed.global', [
            'posts' => $posts,
            'categories' => $categories,
            'currentSort' => $sort,
            'currentType' => $request->type,
            'currentCategory' => $request->category,
        ]);
    }
}
