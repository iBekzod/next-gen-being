<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::active()
            ->whereHas('publishedPosts')
            ->withCount(['publishedPosts'])
            ->ordered()
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'posts_count' => $category->published_posts_count,
                ];
            });


        return response()->json([
            'data' => $categories,
            'message' => 'Categories retrieved successfully'
        ]);
    }

    /**
     * Display the specified category with its posts.
     */
    public function show(Category $category, Request $request): JsonResponse
    {
        if (!$category->is_active) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'latest');

        $postsQuery = $category->publishedPosts()
            ->with(['author', 'tags'])
            ->withCount(['likes', 'comments' => fn($q) => $q->approved()]);

        // Apply sorting
        switch ($sortBy) {
            case 'popular':
                $postsQuery->orderByDesc('views_count')->orderByDesc('likes_count');
                break;
            case 'trending':
                $postsQuery->where('published_at', '>=', now()->subDays(7))
                    ->orderByDesc('views_count');
                break;
            case 'oldest':
                $postsQuery->orderBy('published_at');
                break;
            default:
                $postsQuery->orderByDesc('published_at');
        }

        $posts = $postsQuery->paginate($perPage);

        // Transform the posts
        $posts->getCollection()->transform(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'featured_image' => $post->featured_image,
                'published_at' => $post->published_at->toISOString(),
                'read_time' => $post->read_time,
                'is_premium' => $post->is_premium,
                'views_count' => $post->views_count,
                'likes_count' => $post->likes_count,
                'comments_count' => $post->comments_count,
                'author' => [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                    'avatar' => $post->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name),
                ],
                'tags' => $post->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ]),
            ];
        });

        return response()->json([
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'color' => $category->color,
                    'icon' => $category->icon,
                ],
                'posts' => $posts->items(),
                'pagination' => [
                    'total' => $posts->total(),
                    'per_page' => $posts->perPage(),
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'from' => $posts->firstItem(),
                    'to' => $posts->lastItem(),
                ],
            ],
            'message' => 'Category retrieved successfully'
        ]);
    }
}
