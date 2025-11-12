<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Search posts with advanced filters
     */
    public function search(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Post::published()
            ->with(['category', 'tags', 'author'])
            ->withCount('comments');

        // Text search
        if (!empty($filters['q'])) {
            $searchTerm = $filters['q'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('excerpt', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('content', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Category filter
        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $query->whereIn('category_id', $filters['categories']);
        }

        // Tag filter
        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }

        // Author filter
        if (!empty($filters['authors']) && is_array($filters['authors'])) {
            $query->whereIn('author_id', $filters['authors']);
        }

        // Content type filter
        if (!empty($filters['content_type'])) {
            $query->where('post_type', $filters['content_type']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->where('published_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('published_at', '<=', $filters['date_to']);
        }

        // View count filter
        if (!empty($filters['min_views'])) {
            $query->where('views_count', '>=', (int) $filters['min_views']);
        }
        if (!empty($filters['max_views'])) {
            $query->where('views_count', '<=', (int) $filters['max_views']);
        }

        // Engagement filter
        if (!empty($filters['engagement'])) {
            $query = $this->filterByEngagement($query, $filters['engagement']);
        }

        // Read time filter
        if (!empty($filters['read_time'])) {
            $query = $this->filterByReadTime($query, $filters['read_time']);
        }

        // Is premium filter
        if (isset($filters['is_premium'])) {
            $query->where('is_premium', (bool) $filters['is_premium']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'relevant';
        $query = $this->applySorting($query, $sortBy);

        // Pagination
        $perPage = (int) ($filters['per_page'] ?? 15);
        $page = (int) ($filters['page'] ?? 1);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get available filters for display
     */
    public function getAvailableFilters(): array
    {
        return [
            'categories' => Category::active()->get(['id', 'name', 'slug']),
            'tags' => Tag::withCount('posts')
                ->orderByDesc('posts_count')
                ->limit(50)
                ->get(['id', 'name', 'slug']),
            'authors' => User::whereHas('posts', function ($q) {
                $q->where('status', 'published');
            })
                ->withCount(['posts' => function ($q) {
                    $q->where('status', 'published');
                }])
                ->orderByDesc('posts_count')
                ->limit(30)
                ->get(['id', 'name', 'username', 'avatar']),
            'content_types' => [
                'article' => 'Articles',
                'video_blog' => 'Video Blog',
                'tutorial' => 'Tutorials',
                'news' => 'News',
            ],
            'read_times' => [
                'short' => 'Quick Read (< 5 min)',
                'medium' => 'Medium (5-15 min)',
                'long' => 'Long Read (15+ min)',
            ],
            'engagement_levels' => [
                'trending' => 'Trending (High engagement)',
                'popular' => 'Popular (Medium-high)',
                'new' => 'New (Just published)',
            ],
            'sort_options' => [
                'relevant' => 'Most Relevant',
                'newest' => 'Newest First',
                'oldest' => 'Oldest First',
                'most_viewed' => 'Most Viewed',
                'least_viewed' => 'Least Viewed',
                'most_liked' => 'Most Liked',
                'most_commented' => 'Most Commented',
                'trending' => 'Trending',
            ],
        ];
    }

    /**
     * Get trending search terms
     */
    public function getTrendingSearches($limit = 10): Collection
    {
        // This would typically come from a search_logs table
        // For now, return trending tags
        return Tag::withCount('posts')
            ->orderByDesc('posts_count')
            ->limit($limit)
            ->get(['id', 'name', 'slug']);
    }

    /**
     * Get search suggestions based on query
     */
    public function getSuggestions(string $query, $limit = 10): array
    {
        $query = trim($query);
        if (strlen($query) < 1) {
            return [];
        }

        $suggestions = [];

        // Post suggestions
        $posts = Post::published()
            ->where('title', 'LIKE', "%{$query}%")
            ->limit(3)
            ->get(['id', 'title', 'slug']);

        foreach ($posts as $post) {
            $suggestions[] = [
                'type' => 'post',
                'title' => $post->title,
                'url' => route('posts.show', $post->slug),
            ];
        }

        // Category suggestions
        $categories = Category::active()
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(2)
            ->get(['id', 'name', 'slug']);

        foreach ($categories as $category) {
            $suggestions[] = [
                'type' => 'category',
                'title' => $category->name,
                'url' => route('categories.show', $category->slug),
            ];
        }

        // Tag suggestions
        $tags = Tag::where('name', 'LIKE', "%{$query}%")
            ->limit(3)
            ->get(['id', 'name', 'slug']);

        foreach ($tags as $tag) {
            $suggestions[] = [
                'type' => 'tag',
                'title' => $tag->name,
                'url' => route('tags.show', $tag->slug),
            ];
        }

        // Author suggestions
        $authors = User::where('name', 'LIKE', "%{$query}%")
            ->whereHas('posts', function ($q) {
                $q->where('status', 'published');
            })
            ->limit(2)
            ->get(['id', 'name', 'avatar']);

        foreach ($authors as $author) {
            $suggestions[] = [
                'type' => 'author',
                'title' => $author->name,
                'url' => route('bloggers.profile', $author->id),
                'avatar' => $author->avatar,
            ];
        }

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Filter by engagement level
     */
    private function filterByEngagement($query, string $level)
    {
        return match ($level) {
            'trending' => $query->where('views_count', '>=', 100)
                ->where('likes_count', '>=', 5)
                ->where('published_at', '>=', now()->subDays(7)),
            'popular' => $query->where('views_count', '>=', 50)
                ->where('published_at', '>=', now()->subDays(30)),
            'new' => $query->where('published_at', '>=', now()->subDays(7)),
            default => $query,
        };
    }

    /**
     * Filter by read time
     */
    private function filterByReadTime($query, string $timeRange)
    {
        return match ($timeRange) {
            'short' => $query->where('read_time', '<', 5),
            'medium' => $query->whereBetween('read_time', [5, 15]),
            'long' => $query->where('read_time', '>', 15),
            default => $query,
        };
    }

    /**
     * Apply sorting
     */
    private function applySorting($query, string $sortBy)
    {
        return match ($sortBy) {
            'newest' => $query->orderByDesc('published_at'),
            'oldest' => $query->orderBy('published_at'),
            'most_viewed' => $query->orderByDesc('views_count'),
            'least_viewed' => $query->orderBy('views_count'),
            'most_liked' => $query->orderByDesc('likes_count'),
            'most_commented' => $query->orderByDesc('comments_count'),
            'trending' => $query->orderByDesc('views_count')
                ->where('published_at', '>=', now()->subDays(7)),
            'relevant' => $query->orderByDesc('published_at'), // Default
            default => $query->orderByDesc('published_at'),
        };
    }

    /**
     * Get search statistics
     */
    public function getSearchStats(string $query): array
    {
        $posts = Post::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('excerpt', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%");
            })
            ->get();

        return [
            'total_results' => $posts->count(),
            'total_views' => (int) $posts->sum('views_count'),
            'total_engagement' => (int) ($posts->sum('likes_count') + $posts->sum('comments_count')),
            'avg_read_time' => $posts->count() > 0 ? round($posts->avg('read_time'), 1) : 0,
        ];
    }
}
