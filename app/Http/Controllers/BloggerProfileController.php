<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BloggerProfileController extends Controller
{
    /**
     * Display the blogger's public profile
     */
    public function show(string $username): View
    {
        $blogger = User::where('username', $username)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'blogger');
            })
            ->firstOrFail();

        // Get published posts by this blogger
        $posts = $blogger->posts()
            ->where('status', 'published')
            ->where('moderation_status', 'approved')
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->paginate(12);

        // Get blogger stats
        $stats = [
            'total_posts' => $blogger->posts()->where('status', 'published')->count(),
            'total_followers' => $blogger->followers()->count(),
            'total_following' => $blogger->following()->count(),
            'premium_posts' => $blogger->posts()
                ->where('status', 'published')
                ->where('is_premium', true)
                ->count(),
        ];

        return view('bloggers.profile', [
            'blogger' => $blogger,
            'posts' => $posts,
            'stats' => $stats,
        ]);
    }

    /**
     * Display all bloggers (directory page)
     */
    public function index(Request $request): View
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'blogger');
        })->withCount(['posts' => function ($q) {
            $q->where('status', 'published');
        }, 'followers']);

        // Search by name
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%')
                  ->orWhere('bio', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category (based on their posts)
        if ($request->filled('category')) {
            $query->whereHas('posts', function ($q) use ($request) {
                $q->where('status', 'published')
                  ->where('category_id', $request->category);
            });
        }

        // Filter by minimum followers
        if ($request->filled('min_followers')) {
            $query->having('followers_count', '>=', $request->min_followers);
        }

        // Sort options
        $sort = $request->get('sort', 'popular');
        switch ($sort) {
            case 'followers':
                $query->orderByDesc('followers_count');
                break;
            case 'posts':
                $query->orderByDesc('posts_count');
                break;
            case 'newest':
                $query->latest('created_at');
                break;
            case 'active': // Most recently active
                $query->orderByDesc(function ($q) {
                    $q->select('published_at')
                      ->from('posts')
                      ->whereColumn('user_id', 'users.id')
                      ->where('status', 'published')
                      ->latest()
                      ->limit(1);
                });
                break;
            default: // popular
                $query->orderByDesc('followers_count')
                      ->orderByDesc('posts_count');
        }

        $bloggers = $query->paginate(20);

        // Get top bloggers for sidebar
        $topBloggers = User::whereHas('roles', function ($q) {
            $q->where('name', 'blogger');
        })->withCount('followers')
          ->orderByDesc('followers_count')
          ->limit(5)
          ->get();

        // Get featured/recommended bloggers
        $featuredBloggers = User::whereHas('roles', function ($q) {
            $q->where('name', 'blogger');
        })->whereHas('posts', function ($q) {
            $q->where('status', 'published')
              ->where('moderation_status', 'approved')
              ->where('moderation_score', '>=', 90); // High quality content
        })->withCount('followers')
          ->inRandomOrder()
          ->limit(3)
          ->get();

        // Get available categories
        $categories = \App\Models\Category::withCount(['posts' => function ($q) {
            $q->where('status', 'published');
        }])->having('posts_count', '>', 0)
          ->orderBy('name')
          ->get();

        return view('bloggers.index', [
            'bloggers' => $bloggers,
            'currentSort' => $sort,
            'topBloggers' => $topBloggers,
            'featuredBloggers' => $featuredBloggers,
            'categories' => $categories,
        ]);
    }
}
