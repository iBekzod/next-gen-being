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
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
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
            default: // popular
                $query->orderByDesc('followers_count')
                      ->orderByDesc('posts_count');
        }

        $bloggers = $query->paginate(20);

        return view('bloggers.index', [
            'bloggers' => $bloggers,
            'currentSort' => $sort,
        ]);
    }
}
