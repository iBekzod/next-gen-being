<?php

namespace App\Services;

use App\Models\ScheduledPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ContentCalendarService
{
    /**
     * Create a scheduled post
     */
    public function schedulePost(User $author, array $data): array
    {
        try {
            $scheduledPost = ScheduledPost::create([
                'user_id' => $author->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 160),
                'featured_image_url' => $data['featured_image_url'] ?? null,
                'status' => 'scheduled',
                'scheduled_for' => $data['scheduled_for'],
                'category_id' => $data['category_id'] ?? null,
                'tags' => $data['tags'] ?? [],
                'metadata' => $data['metadata'] ?? [],
            ]);

            Log::info('Post scheduled', [
                'scheduled_post_id' => $scheduledPost->id,
                'author_id' => $author->id,
                'scheduled_for' => $scheduledPost->scheduled_for,
            ]);

            return [
                'success' => true,
                'scheduled_post' => $this->formatScheduledPost($scheduledPost),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to schedule post', [
                'author_id' => $author->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Save as draft
     */
    public function saveDraft(User $author, array $data): array
    {
        try {
            $scheduledPost = ScheduledPost::create([
                'user_id' => $author->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 160),
                'featured_image_url' => $data['featured_image_url'] ?? null,
                'status' => 'draft',
                'scheduled_for' => $data['scheduled_for'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'tags' => $data['tags'] ?? [],
                'metadata' => $data['metadata'] ?? [],
            ]);

            return [
                'success' => true,
                'scheduled_post' => $this->formatScheduledPost($scheduledPost),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to save draft', [
                'author_id' => $author->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update scheduled post
     */
    public function updateScheduledPost(ScheduledPost $scheduled, array $data): array
    {
        try {
            if ($scheduled->isPublished()) {
                return [
                    'success' => false,
                    'error' => 'Cannot modify published posts',
                ];
            }

            $scheduled->update([
                'title' => $data['title'] ?? $scheduled->title,
                'content' => $data['content'] ?? $scheduled->content,
                'excerpt' => $data['excerpt'] ?? $scheduled->excerpt,
                'featured_image_url' => $data['featured_image_url'] ?? $scheduled->featured_image_url,
                'scheduled_for' => $data['scheduled_for'] ?? $scheduled->scheduled_for,
                'category_id' => $data['category_id'] ?? $scheduled->category_id,
                'tags' => $data['tags'] ?? $scheduled->tags,
                'metadata' => $data['metadata'] ?? $scheduled->metadata,
            ]);

            return [
                'success' => true,
                'scheduled_post' => $this->formatScheduledPost($scheduled),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update scheduled post', [
                'scheduled_post_id' => $scheduled->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Reschedule a post
     */
    public function reschedulePost(ScheduledPost $scheduled, string $newDateTime): array
    {
        try {
            if (!$scheduled->reschedule($newDateTime)) {
                return [
                    'success' => false,
                    'error' => 'Cannot reschedule non-scheduled posts',
                ];
            }

            Log::info('Post rescheduled', [
                'scheduled_post_id' => $scheduled->id,
                'new_time' => $newDateTime,
            ]);

            return [
                'success' => true,
                'scheduled_post' => $this->formatScheduledPost($scheduled),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to reschedule post', [
                'scheduled_post_id' => $scheduled->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete scheduled post
     */
    public function deleteScheduledPost(ScheduledPost $scheduled): array
    {
        try {
            if ($scheduled->isPublished()) {
                return [
                    'success' => false,
                    'error' => 'Cannot delete published posts',
                ];
            }

            $scheduled->delete();

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to delete scheduled post', [
                'scheduled_post_id' => $scheduled->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Publish overdue posts (called by scheduler)
     */
    public function publishOverduePosts(): array
    {
        try {
            $overdueCount = 0;
            $failedCount = 0;

            ScheduledPost::overdue()
                ->where('scheduled_for', '<=', now())
                ->get()
                ->each(function ($scheduledPost) use (&$overdueCount, &$failedCount) {
                    if ($scheduledPost->publish()) {
                        $overdueCount++;
                        Log::info('Overdue post published', ['scheduled_post_id' => $scheduledPost->id]);
                    } else {
                        $failedCount++;
                    }
                });

            return [
                'success' => true,
                'published_count' => $overdueCount,
                'failed_count' => $failedCount,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to publish overdue posts', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get author's calendar
     */
    public function getAuthorCalendar(User $author, $month = null, $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $start = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $scheduled = ScheduledPost::byAuthor($author)
            ->whereBetween('scheduled_for', [$start, $end])
            ->get()
            ->groupBy(fn($post) => $post->scheduled_for->format('Y-m-d'));

        $calendar = [];
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $calendar[$dateStr] = [
                'date' => $dateStr,
                'day' => $date->day,
                'posts' => $scheduled[$dateStr]?->map(fn($p) => $this->formatScheduledPost($p))->toArray() ?? [],
                'post_count' => count($scheduled[$dateStr] ?? []),
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'calendar' => $calendar,
        ];
    }

    /**
     * Get upcoming scheduled posts
     */
    public function getUpcomingPosts(User $author, $days = 30, $limit = 50): array
    {
        $upcoming = ScheduledPost::byAuthor($author)
            ->upcoming($days)
            ->orderBy('scheduled_for')
            ->limit($limit)
            ->get();

        return [
            'count' => $upcoming->count(),
            'posts' => $upcoming->map(fn($p) => $this->formatScheduledPost($p))->toArray(),
        ];
    }

    /**
     * Get drafts
     */
    public function getDrafts(User $author, $limit = 50): array
    {
        $drafts = ScheduledPost::byAuthor($author)
            ->draft()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return [
            'count' => $drafts->count(),
            'drafts' => $drafts->map(fn($p) => $this->formatScheduledPost($p))->toArray(),
        ];
    }

    /**
     * Get publishing history
     */
    public function getPublishingHistory(User $author, $days = 30, $limit = 50): array
    {
        $history = ScheduledPost::byAuthor($author)
            ->published()
            ->where('published_at', '>=', now()->subDays($days))
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        return [
            'count' => $history->count(),
            'history' => $history->map(fn($p) => $this->formatScheduledPost($p))->toArray(),
        ];
    }

    /**
     * Get calendar statistics
     */
    public function getCalendarStats(User $author, $days = 30): array
    {
        $now = now();
        $start = $now->copy()->subDays($days);

        $scheduled = ScheduledPost::byAuthor($author)
            ->scheduled()
            ->where('scheduled_for', '>', $now)
            ->count();

        $published = ScheduledPost::byAuthor($author)
            ->published()
            ->where('published_at', '>=', $start)
            ->count();

        $drafts = ScheduledPost::byAuthor($author)
            ->draft()
            ->count();

        $failed = ScheduledPost::byAuthor($author)
            ->failed()
            ->count();

        return [
            'scheduled_count' => $scheduled,
            'published_count' => $published,
            'draft_count' => $drafts,
            'failed_count' => $failed,
            'total_scheduled' => $scheduled + $drafts,
        ];
    }

    /**
     * Get publishing suggestions (optimal times)
     */
    public function getPublishingSuggestions(User $author): array
    {
        // Analyze author's past posts and audience engagement patterns
        $pastPosts = Post::where('user_id', $author->id)
            ->where('published_at', '>=', now()->subDays(90))
            ->get();

        $dayStats = [];
        $hourStats = [];

        foreach ($pastPosts as $post) {
            $day = $post->published_at->dayName;
            $hour = $post->published_at->hour;

            $dayStats[$day] = ($dayStats[$day] ?? 0) + $post->views_count;
            $hourStats[$hour] = ($hourStats[$hour] ?? 0) + $post->views_count;
        }

        arsort($dayStats);
        arsort($hourStats);

        $bestDays = array_slice(array_keys($dayStats), 0, 3);
        $bestHours = array_slice(array_keys($hourStats), 0, 3);

        return [
            'best_days' => $bestDays,
            'best_hours' => $bestHours,
            'sample_size' => $pastPosts->count(),
            'message' => $pastPosts->count() > 0
                ? 'Based on your publishing history'
                : 'No historical data available yet',
        ];
    }

    /**
     * Format scheduled post for response
     */
    private function formatScheduledPost(ScheduledPost $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content_length' => strlen($post->content),
            'word_count' => $post->word_count,
            'estimated_read_time' => $post->estimated_read_time,
            'featured_image_url' => $post->featured_image_url,
            'status' => $post->status,
            'scheduled_for' => $post->scheduled_for?->toIso8601String(),
            'published_at' => $post->published_at?->toIso8601String(),
            'post_id' => $post->post_id,
            'category' => $post->category?->name,
            'tags' => $post->tags ?? [],
            'created_at' => $post->created_at->toIso8601String(),
            'updated_at' => $post->updated_at->toIso8601String(),
        ];
    }
}
