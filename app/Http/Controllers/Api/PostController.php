<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostCollection;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::published()
            ->with(['author', 'category', 'tags'])
            ->withCount(['likes', 'comments' => fn($q) => $q->approved()]);

        // Filtering
        if ($request->has('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', fn($q) => $q->where('slug', $request->tag));
        }

        if ($request->has('author')) {
            $query->whereHas('author', fn($q) => $q->where('id', $request->author));
        }

        if ($request->has('premium')) {
            $query->where('is_premium', $request->boolean('premium'));
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'latest');
        match ($sortBy) {
            'popular' => $query->orderByDesc('views_count')->orderByDesc('likes_count'),
            'trending' => $query->where('published_at', '>=', now()->subDays(7))
                               ->orderByDesc('views_count'),
            'oldest' => $query->orderBy('published_at'),
            default => $query->orderByDesc('published_at'),
        };

        $posts = $query->paginate($request->get('per_page', 15));

        return new PostCollection($posts);
    }

    public function show(Post $post)
    {
        if (!$post->canBeViewedBy(auth('sanctum')->user())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->load(['author', 'category', 'tags', 'comments' => fn($q) => $q->approved()->topLevel()->with('user')]);
        $post->recordView(auth('sanctum')->user());

        return new PostResource($post);
    }

    public function featured()
    {
        $posts = Post::published()
            ->featured()
            ->with(['author', 'category'])
            ->limit(5)
            ->get();

        return PostResource::collection($posts);
    }

    public function popular(Request $request)
    {
        $days = $request->get('days', 7);

        $posts = Post::published()
            ->where('published_at', '>=', now()->subDays($days))
            ->orderByDesc('views_count')
            ->orderByDesc('likes_count')
            ->with(['author', 'category'])
            ->limit(10)
            ->get();

        return PostResource::collection($posts);
    }
}
