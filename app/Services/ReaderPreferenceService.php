<?php

namespace App\Services;

use App\Models\ReaderPreference;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class ReaderPreferenceService
{
    /**
     * Initialize or get reader preferences
     */
    public function getOrCreatePreferences(User $reader): ReaderPreference
    {
        return ReaderPreference::firstOrCreate(
            ['user_id' => $reader->id],
            [
                'preferred_categories' => [],
                'preferred_authors' => [],
                'preferred_tags' => [],
                'disliked_categories' => [],
                'disliked_authors' => [],
                'disliked_tags' => [],
                'content_type_preferences' => [
                    'long_form' => 0,
                    'short_form' => 0,
                    'technical' => 0,
                    'storytelling' => 0,
                    'news' => 0,
                    'educational' => 0,
                    'opinion' => 0,
                    'tutorial' => 0,
                ],
                'reading_patterns' => [
                    'preferred_days' => [],
                    'preferred_hours' => [],
                    'average_session_length' => 0,
                    'reading_frequency' => 'sporadic',
                ],
                'engagement_data' => [
                    'total_posts_read' => 0,
                    'total_time_spent' => 0,
                    'posts_saved' => 0,
                    'comments_written' => 0,
                    'posts_shared' => 0,
                    'average_read_time' => 0,
                ],
            ]
        );
    }

    /**
     * Record reading activity and update preferences
     */
    public function recordReading(User $reader, Post $post, int $timeSpentSeconds = 0): void
    {
        try {
            $preferences = $this->getOrCreatePreferences($reader);

            // Update category preference
            if ($post->category_id) {
                $preferences->addPreferredCategory($post->category_id, 1.5);
            }

            // Update author preference
            if ($post->user_id) {
                $preferences->addPreferredAuthor($post->user_id, 1.2);
            }

            // Update tag preferences
            if ($post->tags) {
                foreach ($post->tags as $tag) {
                    $preferences->addPreferredTag($tag, 1.0);
                }
            }

            // Analyze content type and update preference
            $contentType = $this->classifyContentType($post);
            $contentPrefs = $preferences->content_type_preferences ?? [];
            $contentPrefs[$contentType] = ($contentPrefs[$contentType] ?? 0) + 1;

            // Update reading patterns
            $patterns = $preferences->reading_patterns ?? [];
            $now = now();
            $patterns['preferred_days'][$now->dayName] = ($patterns['preferred_days'][$now->dayName] ?? 0) + 1;
            $patterns['preferred_hours'][$now->hour] = ($patterns['preferred_hours'][$now->hour] ?? 0) + 1;

            if ($timeSpentSeconds > 0) {
                $patterns['average_session_length'] = round(
                    (($patterns['average_session_length'] * 9) + $timeSpentSeconds) / 10
                );
            }

            // Update engagement data
            $engagement = $preferences->engagement_data ?? [];
            $engagement['total_posts_read'] = ($engagement['total_posts_read'] ?? 0) + 1;
            $engagement['total_time_spent'] = ($engagement['total_time_spent'] ?? 0) + $timeSpentSeconds;
            $engagement['average_read_time'] = $engagement['total_posts_read'] > 0
                ? round($engagement['total_time_spent'] / $engagement['total_posts_read'])
                : 0;

            $preferences->update([
                'content_type_preferences' => $contentPrefs,
                'reading_patterns' => $patterns,
                'engagement_data' => $engagement,
                'last_updated' => now(),
            ]);

            Log::info('Reader preferences updated', [
                'reader_id' => $reader->id,
                'post_id' => $post->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update reader preferences', [
                'reader_id' => $reader->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Record post save/bookmark
     */
    public function recordSave(User $reader, Post $post): void
    {
        try {
            $preferences = $this->getOrCreatePreferences($reader);
            $engagement = $preferences->engagement_data ?? [];
            $engagement['posts_saved'] = ($engagement['posts_saved'] ?? 0) + 1;

            $preferences->update([
                'engagement_data' => $engagement,
                'last_updated' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record save', ['reader_id' => $reader->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Record comment
     */
    public function recordComment(User $reader): void
    {
        try {
            $preferences = $this->getOrCreatePreferences($reader);
            $engagement = $preferences->engagement_data ?? [];
            $engagement['comments_written'] = ($engagement['comments_written'] ?? 0) + 1;

            $preferences->update([
                'engagement_data' => $engagement,
                'last_updated' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record comment', ['reader_id' => $reader->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Record share
     */
    public function recordShare(User $reader): void
    {
        try {
            $preferences = $this->getOrCreatePreferences($reader);
            $engagement = $preferences->engagement_data ?? [];
            $engagement['posts_shared'] = ($engagement['posts_shared'] ?? 0) + 1;

            $preferences->update([
                'engagement_data' => $engagement,
                'last_updated' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record share', ['reader_id' => $reader->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Add to preferences (liked content)
     */
    public function likeContent(User $reader, Post $post): void
    {
        $this->recordReading($reader, $post, 30); // Assume 30 seconds of engagement
    }

    /**
     * Remove from preferences (disliked/uninterested)
     */
    public function dislikeContent(User $reader, int $categoryId): void
    {
        try {
            $preferences = $this->getOrCreatePreferences($reader);
            $preferences->addDislikedCategory($categoryId);

            Log::info('Disliked category added', [
                'reader_id' => $reader->id,
                'category_id' => $categoryId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dislike content', ['reader_id' => $reader->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get preference match score for a post
     */
    public function getPostMatchScore(User $reader, Post $post): float
    {
        $preferences = $this->getOrCreatePreferences($reader);
        $score = 0;

        // Check if category is disliked
        if (in_array($post->category_id, $preferences->disliked_categories ?? [])) {
            return 0;
        }

        // Check if author is disliked
        if (in_array($post->user_id, $preferences->disliked_authors ?? [])) {
            return 0;
        }

        $preferredCategories = $preferences->preferred_categories ?? [];
        $preferredAuthors = $preferences->preferred_authors ?? [];

        // Category match (40% weight)
        if (isset($preferredCategories[$post->category_id ?? 0])) {
            $score += 40;
        }

        // Author match (30% weight)
        if (isset($preferredAuthors[$post->user_id ?? 0])) {
            $score += 30;
        }

        // Content type match (20% weight)
        $contentType = $this->classifyContentType($post);
        $contentPrefs = $preferences->content_type_preferences ?? [];
        if (isset($contentPrefs[$contentType]) && $contentPrefs[$contentType] > 0) {
            $score += 20;
        }

        // Tag match (10% weight)
        $preferredTags = $preferences->preferred_tags ?? [];
        if ($post->tags) {
            foreach ($post->tags as $tag) {
                if (isset($preferredTags[$tag])) {
                    $score += 5; // Up to 10%
                    break;
                }
            }
        }

        return min($score, 100); // Cap at 100
    }

    /**
     * Classify post content type based on analysis
     */
    private function classifyContentType(Post $post): string
    {
        $wordCount = str_word_count(strip_tags($post->content));

        // Long-form: > 2000 words
        if ($wordCount > 2000) {
            return 'long_form';
        }

        // Short-form: < 500 words
        if ($wordCount < 500) {
            return 'short_form';
        }

        // Check tags/title for content type indicators
        $title_lower = strtolower($post->title);
        $tags_lower = implode(' ', array_map('strtolower', $post->tags ?? []));
        $content_lower = strtolower($post->content);

        if (str_contains($title_lower, 'tutorial') || str_contains($title_lower, 'how to')
            || str_contains($tags_lower, 'tutorial')) {
            return 'tutorial';
        }

        if (str_contains($title_lower, 'guide') || str_contains($title_lower, 'learn')
            || str_contains($tags_lower, 'educational')) {
            return 'educational';
        }

        if (preg_match('/\$|code|function|class|array|database|api/i', $content_lower)) {
            return 'technical';
        }

        if (str_contains($title_lower, 'story') || str_contains($title_lower, 'experience')
            || str_contains($tags_lower, 'storytelling')) {
            return 'storytelling';
        }

        if (str_contains($title_lower, 'opinion') || str_contains($title_lower, 'thought')
            || str_contains($tags_lower, 'opinion')) {
            return 'opinion';
        }

        // Default to medium-form
        return 'educational';
    }

    /**
     * Get recommended posts for reader
     */
    public function getRecommendedPosts(User $reader, $limit = 20): array
    {
        $preferences = $this->getOrCreatePreferences($reader);
        $topCategories = $preferences->getTopPreferredCategories(5);
        $topAuthors = $preferences->getTopPreferredAuthors(5);

        $posts = Post::query();

        // Filter out disliked content
        if ($preferences->disliked_categories) {
            $posts->whereNotIn('category_id', $preferences->disliked_categories);
        }

        if ($preferences->disliked_authors) {
            $posts->whereNotIn('user_id', $preferences->disliked_authors);
        }

        // Prioritize preferred content
        $posts->where(function ($query) use ($topCategories, $topAuthors) {
            $query->whereIn('category_id', $topCategories)
                ->orWhereIn('user_id', $topAuthors);
        })
        ->orderByDesc('published_at')
        ->limit($limit)
        ->get();

        return $posts->map(function ($post) use ($reader) {
            return [
                'post' => $post,
                'match_score' => $this->getPostMatchScore($reader, $post),
            ];
        })
        ->sortByDesc('match_score')
        ->take($limit)
        ->values()
        ->toArray();
    }

    /**
     * Get preference summary for dashboard
     */
    public function getPreferenceSummary(User $reader): array
    {
        $preferences = $this->getOrCreatePreferences($reader);

        return [
            'top_categories' => $preferences->getTopPreferredCategories(5),
            'top_authors' => $preferences->getTopPreferredAuthors(5),
            'top_tags' => $preferences->getTopPreferredTags(10),
            'disliked_categories' => $preferences->disliked_categories ?? [],
            'disliked_authors' => $preferences->disliked_authors ?? [],
            'content_type_preferences' => $preferences->getContentTypeScores(),
            'reading_times' => $preferences->getBestReadingTimes(),
            'engagement_summary' => $preferences->getEngagementSummary(),
            'last_updated' => $preferences->last_updated?->toIso8601String(),
        ];
    }

    /**
     * Reset preferences
     */
    public function resetPreferences(User $reader): void
    {
        try {
            $preferences = $this->getOrCreatePreferences($reader);
            $preferences->update([
                'preferred_categories' => [],
                'preferred_authors' => [],
                'preferred_tags' => [],
                'disliked_categories' => [],
                'disliked_authors' => [],
                'disliked_tags' => [],
                'content_type_preferences' => [
                    'long_form' => 0,
                    'short_form' => 0,
                    'technical' => 0,
                    'storytelling' => 0,
                    'news' => 0,
                    'educational' => 0,
                    'opinion' => 0,
                    'tutorial' => 0,
                ],
                'reading_patterns' => [
                    'preferred_days' => [],
                    'preferred_hours' => [],
                    'average_session_length' => 0,
                    'reading_frequency' => 'sporadic',
                ],
                'engagement_data' => [
                    'total_posts_read' => 0,
                    'total_time_spent' => 0,
                    'posts_saved' => 0,
                    'comments_written' => 0,
                    'posts_shared' => 0,
                    'average_read_time' => 0,
                ],
            ]);

            Log::info('Reader preferences reset', ['reader_id' => $reader->id]);
        } catch (\Exception $e) {
            Log::error('Failed to reset preferences', ['reader_id' => $reader->id, 'error' => $e->getMessage()]);
        }
    }
}
