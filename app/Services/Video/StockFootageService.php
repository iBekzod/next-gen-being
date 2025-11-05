<?php

namespace App\Services\Video;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class StockFootageService
{
    /**
     * Fetch stock footage for a post
     *
     * @param Post $post
     * @param int $totalDuration Duration in seconds
     * @return array List of video clips with URLs and metadata
     */
    public function fetchFootage(Post $post, int $totalDuration): array
    {
        $apiKey = config('services.pexels.api_key');
        if (!$apiKey) {
            throw new Exception('Pexels API key not configured');
        }

        // Extract keywords from post
        $keywords = $this->extractKeywords($post);

        // Determine how many clips we need
        $clipDuration = 5; // Each clip is ~5 seconds
        $numberOfClips = (int)ceil($totalDuration / $clipDuration);

        $clips = [];
        $usedQueries = [];

        // Fetch diverse footage based on keywords
        foreach ($keywords as $index => $keyword) {
            if (count($clips) >= $numberOfClips) {
                break;
            }

            // Avoid duplicate searches
            if (in_array($keyword, $usedQueries)) {
                continue;
            }

            $videos = $this->searchVideos($keyword, 1);

            if (!empty($videos)) {
                $video = $videos[0];
                $clips[] = [
                    'url' => $video['video_url'],
                    'duration' => $clipDuration,
                    'start_time' => count($clips) * $clipDuration,
                    'keyword' => $keyword,
                    'attribution' => $video['attribution'],
                ];

                $usedQueries[] = $keyword;
            }
        }

        // If we don't have enough clips, add generic tech/coding footage
        while (count($clips) < $numberOfClips) {
            $genericKeywords = ['technology', 'coding', 'computer', 'workspace', 'digital'];
            $keyword = $genericKeywords[array_rand($genericKeywords)];

            if (in_array($keyword, $usedQueries)) {
                continue;
            }

            $videos = $this->searchVideos($keyword, 1);

            if (!empty($videos)) {
                $video = $videos[0];
                $clips[] = [
                    'url' => $video['video_url'],
                    'duration' => $clipDuration,
                    'start_time' => count($clips) * $clipDuration,
                    'keyword' => $keyword,
                    'attribution' => $video['attribution'],
                ];

                $usedQueries[] = $keyword;
            } else {
                break; // No more videos available
            }
        }

        return $clips;
    }

    /**
     * Search for videos on Pexels
     */
    protected function searchVideos(string $query, int $perPage = 5): array
    {
        $apiKey = config('services.pexels.api_key');

        // Cache results for 1 hour to avoid hitting rate limits
        $cacheKey = "pexels_videos_{$query}_{$perPage}";

        return Cache::remember($cacheKey, 3600, function () use ($query, $perPage, $apiKey) {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->get('https://api.pexels.com/videos/search', [
                'query' => $query,
                'per_page' => $perPage,
                'orientation' => 'portrait', // For vertical videos (TikTok, Reels)
                'size' => 'medium',
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $videos = [];

            foreach ($data['videos'] ?? [] as $video) {
                // Get the HD video file
                $videoFile = collect($video['video_files'] ?? [])
                    ->where('quality', 'hd')
                    ->first();

                if (!$videoFile) {
                    // Fallback to any available quality
                    $videoFile = $video['video_files'][0] ?? null;
                }

                if ($videoFile) {
                    $videos[] = [
                        'video_url' => $videoFile['link'],
                        'width' => $videoFile['width'],
                        'height' => $videoFile['height'],
                        'duration' => $video['duration'] ?? 5,
                        'attribution' => "Video by {$video['user']['name']} from Pexels",
                        'photographer' => $video['user']['name'],
                        'pexels_id' => $video['id'],
                    ];
                }
            }

            return $videos;
        });
    }

    /**
     * Extract keywords from post for footage search
     */
    protected function extractKeywords(Post $post): array
    {
        $keywords = [];

        // Add category name
        if ($post->category) {
            $keywords[] = $post->category->name;
        }

        // Add tags
        foreach ($post->tags as $tag) {
            $keywords[] = $tag->name;
        }

        // Extract key terms from title
        $titleWords = explode(' ', $post->title);
        $titleKeywords = array_filter($titleWords, function($word) {
            return strlen($word) > 4; // Only words longer than 4 characters
        });
        $keywords = array_merge($keywords, array_slice($titleKeywords, 0, 3));

        // Extract nouns from excerpt (simplified approach)
        $excerpt = strtolower($post->excerpt);
        $techKeywords = ['laravel', 'php', 'javascript', 'react', 'vue', 'python', 'ai', 'coding', 'programming', 'development'];

        foreach ($techKeywords as $techKeyword) {
            if (str_contains($excerpt, $techKeyword)) {
                $keywords[] = $techKeyword;
            }
        }

        // Remove duplicates and limit to top 10
        $keywords = array_unique($keywords);
        return array_slice($keywords, 0, 10);
    }

    /**
     * Download video to local storage
     */
    public function downloadVideo(string $url): string
    {
        $response = Http::timeout(300)->get($url);

        if (!$response->successful()) {
            throw new Exception("Failed to download video from {$url}");
        }

        $filename = 'stock-footage/' . uniqid('clip_') . '.mp4';
        \Storage::disk('public')->put($filename, $response->body());

        return storage_path('app/public/' . $filename);
    }

    /**
     * Get landscape videos for YouTube
     */
    public function searchLandscapeVideos(string $query, int $perPage = 5): array
    {
        $apiKey = config('services.pexels.api_key');

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
        ])->get('https://api.pexels.com/videos/search', [
            'query' => $query,
            'per_page' => $perPage,
            'orientation' => 'landscape', // For horizontal videos (YouTube)
            'size' => 'large',
        ]);

        if (!$response->successful()) {
            return [];
        }

        $data = $response->json();
        $videos = [];

        foreach ($data['videos'] ?? [] as $video) {
            $videoFile = collect($video['video_files'] ?? [])
                ->where('quality', 'hd')
                ->first();

            if ($videoFile) {
                $videos[] = [
                    'video_url' => $videoFile['link'],
                    'width' => $videoFile['width'],
                    'height' => $videoFile['height'],
                    'duration' => $video['duration'] ?? 10,
                    'attribution' => "Video by {$video['user']['name']} from Pexels",
                ];
            }
        }

        return $videos;
    }
}
