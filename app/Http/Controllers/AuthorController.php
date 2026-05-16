<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function show(string $slug, Request $request): View
    {
        $author = User::where('slug', $slug)->firstOrFail();

        $postsQuery = Post::where('author_id', $author->id)
            ->where('status', 'published')
            ->with(['category', 'tags'])
            ->latest('published_at');

        if ($request->filled('category')) {
            $postsQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        $posts = $postsQuery->paginate(12)->withQueryString();

        $stats = [
            'total_posts' => Post::where('author_id', $author->id)
                ->where('status', 'published')->count(),
            'total_views' => (int) Post::where('author_id', $author->id)
                ->where('status', 'published')
                ->sum('views_count'),
            'total_likes' => (int) Post::where('author_id', $author->id)
                ->where('status', 'published')
                ->sum('likes_count'),
            'member_since' => $author->created_at?->format('M Y'),
        ];

        $categories = Post::where('author_id', $author->id)
            ->where('status', 'published')
            ->with('category')
            ->get()
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->values();

        return view('authors.show', [
            'author' => $author,
            'posts' => $posts,
            'stats' => $stats,
            'categories' => $categories,
            'activeCategory' => $request->category,
        ]);
    }

    public function index(): View
    {
        $authors = User::whereNotNull('slug')
            ->whereHas('posts', fn($q) => $q->where('status', 'published'))
            ->withCount(['posts' => fn($q) => $q->where('status', 'published')])
            ->orderByDesc('posts_count')
            ->get();

        return view('authors.index', compact('authors'));
    }
}
