<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CollectionService
{
    /**
     * Create a new collection
     */
    public function createCollection(User $creator, array $data): array
    {
        try {
            $slug = Str::slug($data['name']);
            $existingCount = Collection::where('slug', $slug)->count();
            if ($existingCount > 0) {
                $slug = $slug . '-' . ($existingCount + 1);
            }

            $collection = Collection::create([
                'user_id' => $creator->id,
                'slug' => $slug,
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'cover_image_url' => $data['cover_image_url'] ?? null,
                'is_public' => $data['is_public'] ?? true,
                'is_featured' => false,
            ]);

            Log::info('Collection created', [
                'collection_id' => $collection->id,
                'creator_id' => $creator->id,
                'name' => $collection->name,
            ]);

            return [
                'success' => true,
                'collection' => $this->formatCollection($collection),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create collection', [
                'creator_id' => $creator->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update collection details
     */
    public function updateCollection(Collection $collection, array $data): array
    {
        try {
            $collection->update([
                'name' => $data['name'] ?? $collection->name,
                'description' => $data['description'] ?? $collection->description,
                'cover_image_url' => $data['cover_image_url'] ?? $collection->cover_image_url,
                'is_public' => $data['is_public'] ?? $collection->is_public,
            ]);

            Log::info('Collection updated', [
                'collection_id' => $collection->id,
            ]);

            return [
                'success' => true,
                'collection' => $this->formatCollection($collection),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update collection', [
                'collection_id' => $collection->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a collection
     */
    public function deleteCollection(Collection $collection): array
    {
        try {
            $collectionId = $collection->id;
            $collection->delete();

            Log::info('Collection deleted', [
                'collection_id' => $collectionId,
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to delete collection', [
                'collection_id' => $collection->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Add post to collection
     */
    public function addPostToCollection(Collection $collection, Post $post): array
    {
        try {
            if ($collection->posts()->where('post_id', $post->id)->exists()) {
                return [
                    'success' => false,
                    'error' => 'Post is already in this collection',
                ];
            }

            $item = $collection->addPost($post);

            Log::info('Post added to collection', [
                'collection_id' => $collection->id,
                'post_id' => $post->id,
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to add post to collection', [
                'collection_id' => $collection->id,
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Remove post from collection
     */
    public function removePostFromCollection(Collection $collection, Post $post): array
    {
        try {
            $collection->removePost($post);

            Log::info('Post removed from collection', [
                'collection_id' => $collection->id,
                'post_id' => $post->id,
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to remove post from collection', [
                'collection_id' => $collection->id,
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Reorder posts in collection
     */
    public function reorderPosts(Collection $collection, array $postIds): array
    {
        try {
            $collection->reorderPosts($postIds);

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to reorder posts', [
                'collection_id' => $collection->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Save/unsave collection for user
     */
    public function toggleSaveCollection(Collection $collection, User $user): array
    {
        try {
            if ($collection->isSavedBy($user)) {
                $collection->savedBy()->detach($user->id);
                $collection->decrementSaves();
            } else {
                $collection->savedBy()->attach($user->id);
                $collection->incrementSaves();
            }

            return [
                'success' => true,
                'saved' => $collection->isSavedBy($user),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to toggle save collection', [
                'collection_id' => $collection->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Increment view count
     */
    public function recordView(Collection $collection): void
    {
        $collection->incrementViews();
    }

    /**
     * Get collection details with posts
     */
    public function getCollectionDetails(Collection $collection): array
    {
        return [
            'id' => $collection->id,
            'name' => $collection->name,
            'slug' => $collection->slug,
            'description' => $collection->description,
            'cover_image_url' => $collection->cover_image_url,
            'is_public' => $collection->is_public,
            'is_featured' => $collection->is_featured,
            'creator' => [
                'id' => $collection->creator->id,
                'name' => $collection->creator->name,
                'username' => $collection->creator->username,
                'profile_image_url' => $collection->creator->profile_image_url,
            ],
            'posts' => $collection->posts->map(fn($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'featured_image_url' => $post->featured_image_url,
                'author_name' => $post->author?->name,
            ])->toArray(),
            'stats' => [
                'post_count' => $collection->posts()->count(),
                'view_count' => $collection->view_count,
                'saved_count' => $collection->saved_count,
                'created_at' => $collection->created_at->toDateString(),
                'updated_at' => $collection->updated_at->toDateString(),
            ],
        ];
    }

    /**
     * Get creator's collections
     */
    public function getCreatorCollections(User $creator, $limit = 20): array
    {
        $collections = Collection::byCreator($creator)
            ->orderByDesc('created_at')
            ->paginate($limit);

        return [
            'collections' => $collections->map(fn($c) => $this->formatCollection($c))->toArray(),
            'pagination' => [
                'total' => $collections->total(),
                'per_page' => $collections->perPage(),
                'current_page' => $collections->currentPage(),
                'last_page' => $collections->lastPage(),
            ],
        ];
    }

    /**
     * Get public collections (discovery)
     */
    public function getPublicCollections($limit = 20, $days = 30): array
    {
        $collections = Collection::public()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('saved_count')
            ->orderByDesc('view_count')
            ->paginate($limit);

        return [
            'collections' => $collections->map(fn($c) => $this->formatCollection($c))->toArray(),
            'pagination' => [
                'total' => $collections->total(),
                'per_page' => $collections->perPage(),
                'current_page' => $collections->currentPage(),
                'last_page' => $collections->lastPage(),
            ],
        ];
    }

    /**
     * Get trending collections
     */
    public function getTrendingCollections($limit = 10, $days = 7): array
    {
        return Collection::trending($days)
            ->limit($limit)
            ->get()
            ->map(fn($c) => $this->formatCollection($c))
            ->toArray();
    }

    /**
     * Get featured collections
     */
    public function getFeaturedCollections($limit = 10): array
    {
        return Collection::featured()
            ->public()
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get()
            ->map(fn($c) => $this->formatCollection($c))
            ->toArray();
    }

    /**
     * Get user's saved collections
     */
    public function getUserSavedCollections(User $user, $limit = 20): array
    {
        $collections = $user->savedCollections()
            ->orderByDesc('saved_collections.created_at')
            ->paginate($limit);

        return [
            'collections' => $collections->map(fn($c) => $this->formatCollection($c))->toArray(),
            'pagination' => [
                'total' => $collections->total(),
                'per_page' => $collections->perPage(),
                'current_page' => $collections->currentPage(),
                'last_page' => $collections->lastPage(),
            ],
        ];
    }

    /**
     * Search collections
     */
    public function searchCollections(string $query, $limit = 20): array
    {
        $collections = Collection::public()
            ->where('name', 'ilike', "%{$query}%")
            ->orWhere('description', 'ilike', "%{$query}%")
            ->orderByDesc('view_count')
            ->paginate($limit);

        return [
            'collections' => $collections->map(fn($c) => $this->formatCollection($c))->toArray(),
            'pagination' => [
                'total' => $collections->total(),
                'per_page' => $collections->perPage(),
                'current_page' => $collections->currentPage(),
                'last_page' => $collections->lastPage(),
            ],
        ];
    }

    /**
     * Get collection stats for dashboard
     */
    public function getCollectionStats(Collection $collection): array
    {
        return [
            'total_posts' => $collection->posts()->count(),
            'total_views' => $collection->view_count,
            'total_saves' => $collection->saved_count,
            'engagement_rate' => $collection->posts()->count() > 0
                ? round(($collection->saved_count / max($collection->view_count, 1)) * 100, 2)
                : 0,
            'average_post_views' => $collection->posts()->count() > 0
                ? round($collection->view_count / $collection->posts()->count(), 2)
                : 0,
        ];
    }

    /**
     * Format collection for API response
     */
    private function formatCollection(Collection $collection): array
    {
        return [
            'id' => $collection->id,
            'name' => $collection->name,
            'slug' => $collection->slug,
            'description' => $collection->description,
            'cover_image_url' => $collection->cover_image_url,
            'is_public' => $collection->is_public,
            'is_featured' => $collection->is_featured,
            'post_count' => $collection->posts()->count(),
            'view_count' => $collection->view_count,
            'saved_count' => $collection->saved_count,
            'creator' => [
                'id' => $collection->creator->id,
                'name' => $collection->creator->name,
                'username' => $collection->creator->username,
                'profile_image_url' => $collection->creator->profile_image_url,
            ],
            'created_at' => $collection->created_at->toIso8601String(),
            'updated_at' => $collection->updated_at->toIso8601String(),
        ];
    }
}
