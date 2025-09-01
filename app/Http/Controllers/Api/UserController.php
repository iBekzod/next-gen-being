<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\UserInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                'bio' => $user->bio,
                'website' => $user->website,
                'twitter' => $user->twitter,
                'linkedin' => $user->linkedin,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'is_premium' => $user->isPremium(),
                'subscription' => $user->subscription ? [
                    'status' => $user->subscription->status,
                    'name' => $user->subscription->name,
                    'renews_at' => $user->subscription->renews_at?->toISOString(),
                    'ends_at' => $user->subscription->ends_at?->toISOString(),
                ] : null,
                'stats' => [
                    'posts_count' => $user->posts()->published()->count(),
                    'total_views' => $user->posts()->sum('views_count'),
                    'total_likes' => $user->posts()->sum('likes_count'),
                ],
                'created_at' => $user->created_at->toISOString(),
            ],
            'message' => 'Profile retrieved successfully'
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'twitter' => 'nullable|string|max:50',
            'linkedin' => 'nullable|string|max:100',
            'avatar' => 'nullable|url|max:255',
            'current_password' => 'required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Handle password update
        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }
            $validated['password'] = Hash::make($validated['password']);
        }

        // Remove current_password from the update array
        unset($validated['current_password']);

        $user->update($validated);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                'bio' => $user->bio,
                'website' => $user->website,
                'twitter' => $user->twitter,
                'linkedin' => $user->linkedin,
            ],
            'message' => 'Profile updated successfully'
        ]);
    }

    /**
     * Get the authenticated user's posts.
     */
    public function posts(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);
        $status = $request->get('status', 'all');

        $query = $user->posts()->with(['category', 'tags']);

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $posts = $query->latest()->paginate($perPage);

        // Transform posts
        $posts->getCollection()->transform(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'status' => $post->status,
                'featured_image' => $post->featured_image,
                'published_at' => $post->published_at?->toISOString(),
                'read_time' => $post->read_time,
                'is_premium' => $post->is_premium,
                'views_count' => $post->views_count,
                'likes_count' => $post->likes_count,
                'comments_count' => $post->comments_count,
                'category' => [
                    'id' => $post->category->id,
                    'name' => $post->category->name,
                    'slug' => $post->category->slug,
                    'color' => $post->category->color,
                ],
                'tags' => $post->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ]),
                'created_at' => $post->created_at->toISOString(),
                'updated_at' => $post->updated_at->toISOString(),
            ];
        });

        return response()->json([
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ],
            'message' => 'Posts retrieved successfully'
        ]);
    }

    /**
     * Get the authenticated user's bookmarked posts.
     */
    public function bookmarks(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);

        $bookmarkedPosts = Post::whereHas('interactions', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('type', 'bookmark');
        })
        ->published()
        ->with(['author', 'category', 'tags'])
        ->withCount(['likes', 'comments' => fn($q) => $q->approved()])
        ->latest()
        ->paginate($perPage);

        // Transform posts
        $bookmarkedPosts->getCollection()->transform(function ($post) {
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
                'category' => [
                    'id' => $post->category->id,
                    'name' => $post->category->name,
                    'slug' => $post->category->slug,
                    'color' => $post->category->color,
                ],
                'tags' => $post->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ]),
            ];
        });

        return response()->json([
            'data' => $bookmarkedPosts->items(),
            'pagination' => [
                'total' => $bookmarkedPosts->total(),
                'per_page' => $bookmarkedPosts->perPage(),
                'current_page' => $bookmarkedPosts->currentPage(),
                'last_page' => $bookmarkedPosts->lastPage(),
                'from' => $bookmarkedPosts->firstItem(),
                'to' => $bookmarkedPosts->lastItem(),
            ],
            'message' => 'Bookmarks retrieved successfully'
        ]);
    }
}
