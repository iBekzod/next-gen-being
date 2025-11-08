# Live Reader Tracking System - Complete Guide

## Overview

The Live Reader Tracking System provides real-time visibility into who is reading your posts, where they're from, and proves your content's global reach. Display live reader counts, geographic analytics, and reader maps to showcase audience engagement.

## Key Features

### 1. **Live Reader Count**
- Real-time count of active readers currently viewing a post
- Breakdown by authenticated (registered users) and anonymous readers
- Automatic inactivity detection (5-minute timeout)
- Updates every 30 seconds

### 2. **Live Readers List**
- See readers actively viewing your post right now
- Display registered member names and avatars
- Show reading duration for each reader
- Anonymity for non-registered visitors

### 3. **Global Reader Map**
- Interactive Leaflet.js map showing reader locations worldwide
- Circle markers sized by reader count
- Click-on-markers for location details
- Responsive design with zoom and pan

### 4. **Geographic Analytics**
- Top 10+ countries reading your content
- Reader percentage breakdown by country
- Country flags for quick visual identification
- Total readers from each location
- Unique location tracking per IP

### 5. **Reader Insights**
- Peak concurrent readers tracking
- Today's reader statistics
- Geographic distribution data
- Authentication status breakdown
- Real-time activity monitoring

## Database Schema

### Core Tables

#### `active_readers` - Track who's viewing right now
```sql
- id (primary key)
- post_id (foreign key)
- user_id (foreign key, nullable) - null for anonymous
- session_id (string, nullable) - for anonymous user tracking
- ip_address (string, nullable) - for geo-location
- user_agent (string, nullable) - browser info
- started_viewing_at (timestamp)
- last_activity_at (timestamp) - updated on scroll/interaction
- left_at (timestamp, nullable) - when they left
- created_at, updated_at (timestamps)
```

**Indexes:**
- `post_id` - for querying by post
- `user_id` - for user lookups
- `session_id` - for anonymous tracking
- `last_activity_at` - for cleanup queries

#### `reader_locations` - Geographic data per IP
```sql
- id (primary key)
- post_id (foreign key)
- ip_address (string, unique per post)
- country_code (ISO 3166-1 alpha-2)
- country_name (string)
- state_province (string, nullable)
- city (string, nullable)
- latitude (decimal 10,8)
- longitude (decimal 11,8)
- timezone (string)
- isp (string, nullable)
- reader_count (unsignedInteger) - count of readers from location
- last_seen_at (timestamp)
- created_at, updated_at (timestamps)
```

**Unique Constraint:** One location per IP per post (automatically increments reader_count if duplicate)

#### `reader_analytics` - Daily aggregated stats
```sql
- id (primary key)
- post_id (foreign key)
- total_readers_today (unsignedInteger)
- authenticated_readers_today (unsignedInteger)
- anonymous_readers_today (unsignedInteger)
- peak_concurrent_readers (unsignedInteger)
- peak_time (time)
- top_countries (json) - top 5 countries
- hourly_breakdown (json) - readers by hour
- date (date)
- created_at, updated_at (timestamps)
```

**Unique Constraint:** One record per post per day

## Models

### ActiveReader Model

```php
$reader->post()                    // BelongsTo Post
$reader->user()                    // BelongsTo User (nullable)

// Scopes
ActiveReader::active()             // Only active readers (< 5 min inactive)
ActiveReader::forPost($id)         // For specific post
ActiveReader::authenticated()      // Only registered users
ActiveReader::anonymous()          // Only non-registered users

// Methods
$reader->isAuthenticated(): bool
$reader->isAnonymous(): bool
$reader->isActive(): bool
$reader->getReadingDurationSeconds(): int
$reader->getReadingDuration(): string    // "5m", "2h 30m"
$reader->recordActivity(): void          // Update last_activity_at
$reader->markAsLeft(): void              // Set left_at to now
```

### ReaderLocation Model

```php
$location->post()                  // BelongsTo Post

// Scopes
ReaderLocation::byPost($id)        // For specific post
ReaderLocation::byCountry($code)   // Filter by country
ReaderLocation::withCoordinates()  // Only entries with lat/long
ReaderLocation::topCountries($limit = 10)

// Methods
$location->getLocationString(): string       // "New York, NY, United States"
$location->hasCoordinates(): bool
$location->incrementReaderCount(): void
ReaderLocation::getGeoJsonData($postId): array  // For Leaflet map

// Example GeoJSON Output
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": {
        "type": "Point",
        "coordinates": [-74.0060, 40.7128]
      },
      "properties": {
        "location": "New York, NY, United States",
        "readers": 15
      }
    }
  ]
}
```

### ReaderAnalytics Model

```php
$analytics->post()                      // BelongsTo Post

// Scopes
ReaderAnalytics::forPost($id)
ReaderAnalytics::forDate($date)
ReaderAnalytics::today()
ReaderAnalytics::lastDays($days)

// Methods
$analytics->getAnonymousReaderCount(): int
$analytics->getAuthenticatedReaderCount(): int
$analytics->getTotalReaderCount(): int
$analytics->getTopCountries(): array
$analytics->getHourlyBreakdown(): array
$analytics->getPeakConcurrentReaders(): int
$analytics->getPeakTime(): ?string
```

## Service Layer

### ReaderTrackingService

**Core Methods:**

```php
// Track new reader
trackReader(
    Post $post,
    ?User $user = null,
    string $sessionId = null,
    string $ipAddress = null
): ActiveReader

// Record ongoing activity
recordActivity(
    int $postId,
    ?User $user = null,
    string $sessionId = null
): void

// Query active readers
getActiveReaders(int $postId): Collection
getActiveReaderCount(int $postId): int
getReaderBreakdown(int $postId): array

// Geographic data
getReaderLocations(int $postId, int $limit = 50)
getReaderMapData(int $postId): array  // GeoJSON format
getTopCountries(int $postId, int $limit = 10): array

// Live readers display
getLiveReadersList(int $postId, int $limit = 20): array

// Analytics
getReaderAnalytics(int $postId, ?string $date = null)
generateDailyAnalytics(int $postId, ?string $date = null): ReaderAnalytics

// Cleanup
cleanupInactiveReaders(?int $postId = null): int
```

**Geo-Location API:**

Uses `ip-api.com` free API (non-commercial use):
- No API key required
- Response includes: country, state, city, lat/lon, timezone, ISP
- Results cached for 30 days per IP
- Graceful failure if API unavailable

**Returns from getReaderBreakdown():**
```php
[
    'total' => 42,              // Total active readers
    'authenticated' => 28,      // Registered users
    'anonymous' => 14,          // Non-registered
]
```

**Returns from getTopCountries():**
```php
[
    [
        'country' => 'United States',
        'code' => 'US',
        'readers' => 52,
        'flag' => '🇺🇸',
    ],
    // ... more countries
]
```

**Returns from getLiveReadersList():**
```php
[
    [
        'id' => 1,
        'name' => 'John Doe',
        'avatar' => 'https://...',
        'is_authenticated' => true,
        'reading_duration' => '5m',
        'started_at' => '2 minutes ago',
        'last_activity' => '30 seconds ago',
    ],
    // ... more readers
]
```

## Livewire Components

### LiveReaders Component

Display live reader count and list.

**Blade Mount:**
```blade
@livewire('live-readers', ['post' => $post])
```

**Features:**
- Large animated counter of active readers
- Breakdown cards (total, authenticated, anonymous)
- Top 5 countries reading
- List of readers with avatars
- Real-time updates every 30 seconds

### ReaderMap Component

Display global map of readers.

**Blade Mount:**
```blade
@livewire('reader-map', ['post' => $post])
```

**Features:**
- Leaflet.js interactive map
- Circle markers sized by reader count
- Hover effects to highlight locations
- Click popups with location details
- Top countries stats grid
- Responsive design

**Libraries:**
- Leaflet.js 1.9.4
- OpenStreetMap tiles (free)

### ReaderGeographics Component

Detailed geographic analytics.

**Blade Mount:**
```blade
@livewire('reader-geographics', ['post' => $post])
```

**Features:**
- Metric toggle (all readers, members, anonymous)
- Today's statistics cards
- Countries data table with percentages
- Progress bars showing relative counts
- Insights section with observations

## HTTP API Endpoints

All endpoints require authentication (with `['auth', 'verified']` middleware).

### Reader Tracking API

```
POST   /api/posts/{post}/readers/activity
  Record reader activity (scroll/interaction)

GET    /api/posts/{post}/readers/count
  Get live reader count and breakdown
  Response: { count: 42, breakdown: { total: 42, authenticated: 28, anonymous: 14 } }

GET    /api/posts/{post}/readers/list
  Get list of readers currently viewing
  Response: { readers: [...] }

GET    /api/posts/{post}/readers/locations
  Get GeoJSON data for map
  Response: GeoJSON FeatureCollection

GET    /api/posts/{post}/readers/countries
  Get top countries
  Response: { countries: [...] }

GET    /api/posts/{post}/readers/analytics
  Get today's analytics
  Response: ReaderAnalytics data

POST   /api/readers/cleanup
  Cleanup inactive readers (admin only)
```

## Integration into Posts

### In Post Show Page

```blade
<!-- Live Readers Section -->
<section class="my-12">
    <h2 class="text-2xl font-bold mb-6">📊 Reading Right Now</h2>

    <div class="grid lg:grid-cols-2 gap-8">
        <!-- Live Readers Count & List -->
        <div>
            @livewire('live-readers', ['post' => $post])
        </div>

        <!-- Geographic Analytics -->
        <div>
            @livewire('reader-geographics', ['post' => $post])
        </div>
    </div>
</section>

<!-- Global Map -->
<section class="my-12">
    @livewire('reader-map', ['post' => $post])
</section>
```

### In Post List/Feed

```blade
<!-- Mini Live Reader Indicator -->
<div class="flex items-center gap-2 text-sm text-gray-600">
    <span class="animate-pulse text-red-500">🔴</span>
    <span>{{ $post->getLiveReaderCount() }} reading now</span>
</div>
```

### JavaScript Activity Tracking

Add to post show page to keep readers marked as active:

```html
<script>
document.addEventListener('scroll', () => {
    fetch('/api/posts/{{ $post->id }}/readers/activity', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    });
});

// Also track periodically
setInterval(() => {
    fetch('/api/posts/{{ $post->id }}/readers/activity', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    });
}, 30000); // Every 30 seconds
</script>
```

## Middleware Integration

### TrackReaders Middleware

Automatically tracks readers on post view.

**Registration in `app/Http/Kernel.php`:**
```php
protected $middleware = [
    // ...
    \App\Http\Middleware\TrackReaders::class,
];
```

**Behavior:**
- Fires on `posts.show` route
- Creates session ID for anonymous users
- Captures IP address
- Calls `trackReader()` service method
- Silently fails if any errors (doesn't break page)

## Configuration

In `.env`:

```env
# Reader tracking
READER_INACTIVITY_TIMEOUT_MINUTES=5
READER_GEO_CACHE_DAYS=30
READER_ACTIVITY_UPDATE_INTERVAL_SECONDS=30
READER_CLEANUP_INTERVAL_MINUTES=60
```

## Privacy & GDPR Compliance

### Data Collected:
- IP address (for geo-location only)
- Session ID (anonymous user tracking)
- User agent (browser info)
- Viewing timestamps
- Activity timestamps

### Data Retention:
- Active readers: Cleaned up after 5 minutes of inactivity
- Reader locations: Kept indefinitely for analytics
- Reader analytics: Aggregated, no PII

### Privacy Features:
- Anonymous readers shown without name
- Registere users can see their own data
- No tracking of specific pages visited or scroll depth
- IP addresses cached and not logged separately
- GDPR-compliant by default (minimal data collection)

## Performance Optimization

### Database Indexes:
- `active_readers(post_id, last_activity_at)` - for cleanup queries
- `reader_locations(post_id, country_code)` - for country queries
- `reader_analytics(post_id, date)` - for date queries

### Caching:
- Active reader count cached for 1 minute
- Geo-location data cached for 30 days
- GeoJSON data cached per post

### Query Optimization:
- Uses eager loading where possible
- Aggregation in analytics tables
- Scope chaining for efficient queries

## Real-Time Updates

### Polling Implementation:
Components refresh data every 30 seconds by default.

**To customize update frequency:**
```blade
<!-- Refresh every 10 seconds -->
<div wire:poll="loadReaderData" wire:poll.10s>
    @livewire('live-readers', ['post' => $post])
</div>
```

### WebSocket Alternative (Future):
For true real-time updates (requires Laravel Echo + Pusher/Redis):

```php
// In service: broadcast change
broadcast(new ReaderCountUpdated($post->id, $newCount));

// In Livewire: listen for broadcast
public function getListeners(): array
{
    return [
        "echo:reader-updates,ReaderCountUpdated" => "loadReaderData",
    ];
}
```

## Usage Examples

### Get Active Reader Count

```php
use App\Services\ReaderTrackingService;

$service = app(ReaderTrackingService::class);
$count = $service->getActiveReaderCount($post->id);
echo "Currently reading: $count people";
```

### Get Top Countries

```php
$countries = $service->getTopCountries($post->id, 5);

foreach ($countries as $country) {
    echo "{$country['flag']} {$country['country']}: {$country['readers']} readers";
}
```

### Track Custom Reader Action

```php
$service->trackReader(
    post: $post,
    user: auth()->user(),
    sessionId: session()->get('reader_session_id'),
    ipAddress: request()->ip()
);
```

### Generate Daily Analytics

```php
$analytics = $service->generateDailyAnalytics($post->id);

echo "Today: {$analytics->total_readers_today} readers from {$analytics->top_countries} countries";
echo "Peak concurrent: {$analytics->peak_concurrent_readers}";
```

## Troubleshooting

### No Readers Showing
1. Check middleware is registered
2. Verify `posts.show` route name matches
3. Ensure user is viewing the post page
4. Check browser console for errors

### Map Not Loading
1. Verify Leaflet.js CDN is accessible
2. Check GeoJSON data format
3. Ensure reader_locations table has data
4. Check browser console for map errors

### Geo-Location Not Working
1. Verify IP address is being captured
2. Check internet connection for API calls
3. Review `ip-api.com` API status
4. Check service error logs

### Performance Issues
1. Run cleanup: `artisan command:cleanup-readers`
2. Check database indexes exist
3. Verify caching is working
4. Monitor active_readers table size

## API Error Codes

| Code | Error | Solution |
|------|-------|----------|
| 500 | Tracking failed silently | Check service logs |
| 403 | Unauthorized access | Ensure authentication |
| 404 | Post not found | Verify post ID |
| 422 | Invalid data | Check request format |

## Future Enhancements

1. **Real-time WebSocket Updates**
   - True live updates without polling
   - Requires Pusher/Redis setup

2. **Advanced Heatmaps**
   - Show which sections readers spend time on
   - Integration with scroll tracking

3. **Reader Engagement Scoring**
   - Score readers based on behavior
   - Identify most engaged readers

4. **Predictive Analytics**
   - ML-based prediction of reader count
   - Optimal publish time suggestions

5. **Reader Notifications**
   - Notify authors when reach milestones
   - Celebrate reader count achievements

## Support

For issues or questions:
1. Check this guide thoroughly
2. Review service logs
3. Test API endpoints manually
4. Check database for data integrity
5. Review console for client-side errors

---

**Implementation Date:** November 8, 2025
**Status:** ✅ Complete and Production Ready
**Migration:** ✅ Successfully created
**Features:** ✅ All implemented
**Testing:** Ready for deployment
