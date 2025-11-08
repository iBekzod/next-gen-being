<?php

namespace App\Services;

use App\Models\Post;
use App\Models\ActiveReader;
use App\Models\ReaderLocation;
use App\Models\ReaderAnalytics;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ReaderTrackingService
{
    /**
     * Track a new reader viewing a post
     */
    public function trackReader(Post $post, ?User $user = null, string $sessionId = null, string $ipAddress = null): ActiveReader
    {
        // Clean up old inactive readers first
        $this->cleanupInactiveReaders($post->id);

        $reader = ActiveReader::create([
            'post_id' => $post->id,
            'user_id' => $user?->id,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => request()->header('User-Agent'),
            'started_viewing_at' => now(),
            'last_activity_at' => now(),
        ]);

        // Get geo-location if we have an IP
        if ($ipAddress) {
            $this->recordReaderLocation($post, $ipAddress);
        }

        // Increment view count
        $post->increment('views_count');

        // Cache the active reader count
        $this->updateCachedReaderCount($post->id);

        return $reader;
    }

    /**
     * Record reader activity (keep them marked as active)
     */
    public function recordActivity(int $postId, ?User $user = null, string $sessionId = null): void
    {
        if ($user) {
            ActiveReader::wherePostId($postId)
                ->whereUserId($user->id)
                ->whereNull('left_at')
                ->update(['last_activity_at' => now()]);
        } elseif ($sessionId) {
            ActiveReader::wherePostId($postId)
                ->whereSessionId($sessionId)
                ->whereNull('left_at')
                ->update(['last_activity_at' => now()]);
        }

        $this->updateCachedReaderCount($postId);
    }

    /**
     * Get active readers for a post
     */
    public function getActiveReaders(int $postId): \Illuminate\Database\Eloquent\Collection
    {
        return ActiveReader::forPost($postId)
            ->active()
            ->with('user')
            ->get();
    }

    /**
     * Get active reader count for a post
     */
    public function getActiveReaderCount(int $postId): int
    {
        return Cache::remember(
            "active_readers_count_{$postId}",
            now()->addMinutes(1),
            function () use ($postId) {
                return ActiveReader::forPost($postId)->active()->count();
            }
        );
    }

    /**
     * Get authenticated vs anonymous reader counts
     */
    public function getReaderBreakdown(int $postId): array
    {
        $activeReaders = $this->getActiveReaders($postId);

        return [
            'total' => $activeReaders->count(),
            'authenticated' => $activeReaders->filter(fn($r) => $r->isAuthenticated())->count(),
            'anonymous' => $activeReaders->filter(fn($r) => $r->isAnonymous())->count(),
        ];
    }

    /**
     * Get reader locations for a post
     */
    public function getReaderLocations(int $postId, int $limit = 50)
    {
        return ReaderLocation::byPost($postId)
            ->topCountries($limit)
            ->get();
    }

    /**
     * Get geographic JSON for map display
     */
    public function getReaderMapData(int $postId): array
    {
        return ReaderLocation::getGeoJsonData($postId);
    }

    /**
     * Get top countries reading a post
     */
    public function getTopCountries(int $postId, int $limit = 10): array
    {
        return ReaderLocation::byPost($postId)
            ->orderByDesc('reader_count')
            ->limit($limit)
            ->get()
            ->map(fn($location) => [
                'country' => $location->country_name,
                'code' => $location->country_code,
                'readers' => $location->reader_count,
                'flag' => $this->getCountryFlag($location->country_code),
            ])
            ->toArray();
    }

    /**
     * Get reader analytics for a post
     */
    public function getReaderAnalytics(int $postId, ?string $date = null)
    {
        $date = $date ? \Carbon\Carbon::parse($date) : today();

        return ReaderAnalytics::forPost($postId)
            ->forDate($date)
            ->first();
    }

    /**
     * Get live reader list with details
     */
    public function getLiveReadersList(int $postId, int $limit = 20): array
    {
        $readers = ActiveReader::forPost($postId)
            ->active()
            ->with('user')
            ->latest('last_activity_at')
            ->limit($limit)
            ->get();

        return $readers->map(fn($reader) => [
            'id' => $reader->id,
            'name' => $reader->user?->name ?? 'Anonymous Reader',
            'avatar' => $reader->user?->getFirstMediaUrl('avatars'),
            'is_authenticated' => $reader->isAuthenticated(),
            'reading_duration' => $reader->getReadingDuration(),
            'started_at' => $reader->started_viewing_at->diffForHumans(),
            'last_activity' => $reader->last_activity_at->diffForHumans(),
        ])->toArray();
    }

    /**
     * Record reader location from IP address
     */
    private function recordReaderLocation(Post $post, string $ipAddress): void
    {
        try {
            // Check if we already have location data for this IP on this post
            $existingLocation = ReaderLocation::where('post_id', $post->id)
                ->where('ip_address', $ipAddress)
                ->first();

            if ($existingLocation) {
                $existingLocation->incrementReaderCount();
                return;
            }

            // Get geo-location data from IP
            $geoData = $this->getGeoLocationData($ipAddress);

            ReaderLocation::create([
                'post_id' => $post->id,
                'ip_address' => $ipAddress,
                'country_code' => $geoData['country_code'] ?? null,
                'country_name' => $geoData['country_name'] ?? null,
                'state_province' => $geoData['state_province'] ?? null,
                'city' => $geoData['city'] ?? null,
                'latitude' => $geoData['latitude'] ?? null,
                'longitude' => $geoData['longitude'] ?? null,
                'timezone' => $geoData['timezone'] ?? null,
                'isp' => $geoData['isp'] ?? null,
                'reader_count' => 1,
                'last_seen_at' => now(),
            ]);

        } catch (\Exception $e) {
            // Silently fail - don't break the app if geo-location fails
            \Log::warning("Failed to record reader location: " . $e->getMessage());
        }
    }

    /**
     * Get geo-location data from IP address
     * Using ip-api.com (free tier, no API key needed)
     */
    private function getGeoLocationData(string $ipAddress): array
    {
        // Check cache first
        $cacheKey = "geo_location_{$ipAddress}";
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            // Using ip-api.com free API (non-commercial)
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ipAddress}", [
                'fields' => 'status,country,countryCode,state,city,lat,lon,timezone,isp',
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json();

                $result = [
                    'country_code' => $data['countryCode'] ?? null,
                    'country_name' => $data['country'] ?? null,
                    'state_province' => $data['state'] ?? null,
                    'city' => $data['city'] ?? null,
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'isp' => $data['isp'] ?? null,
                ];

                // Cache for 30 days
                Cache::put($cacheKey, $result, now()->addDays(30));

                return $result;
            }

        } catch (\Exception $e) {
            \Log::warning("Geo-location API error for IP {$ipAddress}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Clean up inactive readers (not active in 5 minutes)
     */
    public function cleanupInactiveReaders(?int $postId = null): int
    {
        $query = ActiveReader::where('last_activity_at', '<', now()->subMinutes(5))
            ->whereNull('left_at');

        if ($postId) {
            $query->where('post_id', $postId);
        }

        $count = $query->count();
        $query->update(['left_at' => now()]);

        return $count;
    }

    /**
     * Update cached reader count
     */
    private function updateCachedReaderCount(int $postId): void
    {
        Cache::forget("active_readers_count_{$postId}");
        $this->getActiveReaderCount($postId); // Regenerate cache
    }

    /**
     * Generate analytics for a post
     */
    public function generateDailyAnalytics(int $postId, ?string $date = null): ReaderAnalytics
    {
        $date = $date ? \Carbon\Carbon::parse($date) : today();

        // Get all readers for the day
        $readers = ActiveReader::forPost($postId)
            ->whereDate('started_viewing_at', $date)
            ->get();

        // Calculate metrics
        $totalReaders = $readers->count();
        $authenticatedReaders = $readers->filter(fn($r) => $r->isAuthenticated())->count();
        $anonymousReaders = $readers->filter(fn($r) => $r->isAnonymous())->count();

        // Get peak concurrent
        $peakConcurrent = $this->calculatePeakConcurrentReaders($postId, $date);

        // Get top countries
        $topCountries = ReaderLocation::byPost($postId)
            ->whereDate('last_seen_at', $date)
            ->orderByDesc('reader_count')
            ->limit(5)
            ->get()
            ->map(fn($loc) => [
                'country' => $loc->country_name,
                'count' => $loc->reader_count,
            ])
            ->toArray();

        // Create or update analytics
        return ReaderAnalytics::updateOrCreate(
            [
                'post_id' => $postId,
                'date' => $date,
            ],
            [
                'total_readers_today' => $totalReaders,
                'authenticated_readers_today' => $authenticatedReaders,
                'anonymous_readers_today' => $anonymousReaders,
                'peak_concurrent_readers' => $peakConcurrent,
                'top_countries' => $topCountries,
            ]
        );
    }

    /**
     * Calculate peak concurrent readers
     */
    private function calculatePeakConcurrentReaders(int $postId, $date): int
    {
        // Get readers active during the day
        return ActiveReader::forPost($postId)
            ->whereDate('started_viewing_at', $date)
            ->where(function ($query) {
                $query->whereNull('left_at')
                    ->orWhereDate('left_at', today());
            })
            ->count();
    }

    /**
     * Get country flag emoji from country code
     */
    private function getCountryFlag(string $countryCode): string
    {
        if (!$countryCode || strlen($countryCode) !== 2) {
            return '🌍';
        }

        return '🇦' . chr(64 + ord(substr($countryCode, 0, 1))) . chr(64 + ord(substr($countryCode, 1, 1)));
    }
}
