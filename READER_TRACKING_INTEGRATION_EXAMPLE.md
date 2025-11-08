# Live Reader Tracking - Integration Examples

Quick reference for adding live reader tracking to your post pages.

## 1. Basic Integration in Post Show Page

### Complete Example

```blade
<!-- resources/views/posts/show.blade.php -->
@extends('layouts.app')

@section('content')
<article class="max-w-4xl mx-auto px-6 py-12">
    <!-- Post Header -->
    <header class="mb-8">
        <h1 class="text-4xl font-bold mb-4">{{ $post->title }}</h1>
        <p class="text-gray-600 dark:text-gray-400">
            By {{ $post->author->name }} • {{ $post->published_at->format('M d, Y') }}
        </p>
    </header>

    <!-- Live Reader Indicator (Sticky) -->
    <div class="sticky top-4 mb-8 z-20">
        @livewire('live-readers', ['post' => $post])
    </div>

    <!-- Post Content -->
    <div class="prose dark:prose-invert max-w-none mb-12">
        {!! $post->content !!}
    </div>

    <!-- Reader Analytics -->
    <section class="border-t pt-12 mb-12">
        <h2 class="text-2xl font-bold mb-6">📊 Readers from Around the World</h2>
        @livewire('reader-geographics', ['post' => $post])
    </section>

    <!-- Global Map -->
    <section class="border-t pt-12">
        <h2 class="text-2xl font-bold mb-6">🗺️ Geographic Distribution</h2>
        @livewire('reader-map', ['post' => $post])
    </section>
</article>

<!-- Keep readers active with periodic tracking -->
<script>
    // Record activity every 30 seconds
    setInterval(() => {
        fetch('/api/posts/{{ $post->id }}/readers/activity', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
    }, 30000);

    // Also track on scroll
    let scrollTimeout;
    document.addEventListener('scroll', () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            fetch('/api/posts/{{ $post->id }}/readers/activity', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
        }, 2000);
    });
</script>
@endsection
```

## 2. Post List Integration

### Show Live Reader Count in Feed

```blade
<!-- resources/views/posts/list.blade.php -->
@foreach($posts as $post)
<article class="bg-white dark:bg-slate-800 rounded-lg p-6 border border-gray-200 dark:border-slate-700">
    <!-- Post Title and Excerpt -->
    <h3 class="text-xl font-bold mb-2">
        <a href="{{ route('posts.show', $post) }}" class="hover:text-blue-600">
            {{ $post->title }}
        </a>
    </h3>

    <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $post->excerpt }}</p>

    <!-- Metadata with Live Reader Count -->
    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
        <div class="flex items-center gap-4">
            <span>By {{ $post->author->name }}</span>
            <span>{{ $post->published_at->format('M d, Y') }}</span>
        </div>

        <!-- Live Reader Indicator -->
        @php
            $liveCount = $post->getLiveReaderCount();
        @endphp
        @if($liveCount > 0)
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full text-xs font-semibold">
                <span class="animate-pulse">🔴</span>
                {{ $liveCount }} reading
            </span>
        @endif
    </div>
</article>
@endforeach
```

## 3. Dashboard Integration

### Show Recent Post Activity

```blade
<!-- resources/views/dashboard/posts.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-bold mb-8">My Posts</h1>

    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3 px-4">Title</th>
                <th class="text-left py-3 px-4">Status</th>
                <th class="text-right py-3 px-4">Views</th>
                <th class="text-right py-3 px-4">Live Readers</th>
            </tr>
        </thead>
        <tbody>
            @foreach($posts as $post)
            <tr class="border-b hover:bg-gray-50 dark:hover:bg-slate-800">
                <td class="py-3 px-4">
                    <a href="{{ route('posts.show', $post) }}" class="font-medium hover:text-blue-600">
                        {{ $post->title }}
                    </a>
                </td>
                <td class="py-3 px-4">
                    <span class="px-2 py-1 rounded text-sm
                        @if($post->status === 'published')
                            bg-green-100 text-green-800
                        @else
                            bg-yellow-100 text-yellow-800
                        @endif
                    ">
                        {{ ucfirst($post->status) }}
                    </span>
                </td>
                <td class="py-3 px-4 text-right">{{ number_format($post->views_count) }}</td>
                <td class="py-3 px-4 text-right">
                    @php
                        $liveCount = $post->getLiveReaderCount();
                    @endphp
                    @if($liveCount > 0)
                        <span class="inline-flex items-center gap-1 text-red-600 font-semibold">
                            🔴 {{ $liveCount }}
                        </span>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
```

## 4. API Usage in Controller

### Track Readers Programmatically

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\ReaderTrackingService;

class PostController extends Controller
{
    protected ReaderTrackingService $readerTrackingService;

    public function __construct(ReaderTrackingService $readerTrackingService)
    {
        $this->readerTrackingService = $readerTrackingService;
    }

    /**
     * Show a single post with reader tracking
     */
    public function show(Post $post)
    {
        // Track this reader
        $this->readerTrackingService->trackReader(
            post: $post,
            user: auth()->user(),
            sessionId: session()->get('reader_session_id'),
            ipAddress: request()->ip()
        );

        // Get live reader stats
        $liveReaderCount = $this->readerTrackingService->getActiveReaderCount($post->id);
        $readerBreakdown = $this->readerTrackingService->getReaderBreakdown($post->id);
        $topCountries = $this->readerTrackingService->getTopCountries($post->id, 5);

        return view('posts.show', [
            'post' => $post,
            'liveReaderCount' => $liveReaderCount,
            'readerBreakdown' => $readerBreakdown,
            'topCountries' => $topCountries,
        ]);
    }

    /**
     * Get live reader data via API
     */
    public function getLiveReaders(Post $post)
    {
        $readers = $this->readerTrackingService->getLiveReadersList($post->id);
        $count = $this->readerTrackingService->getActiveReaderCount($post->id);

        return response()->json([
            'count' => $count,
            'readers' => $readers,
            'updated_at' => now(),
        ]);
    }
}
```

## 5. Widget for Sidebar

### Reusable Live Readers Widget

```blade
<!-- resources/views/components/live-readers-widget.blade.php -->
<div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg p-4 shadow-lg">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-blue-100">Currently Reading</p>
            @php
                $count = $post->getLiveReaderCount();
            @endphp
            <p class="text-3xl font-bold mt-1">{{ $count }}</p>
            <p class="text-xs text-blue-100 mt-1">
                @if($count > 0)
                    people reading now
                @else
                    Be the first to read!
                @endif
            </p>
        </div>
        <div class="text-5xl opacity-30">👥</div>
    </div>
</div>

<!-- Usage -->
@component('components.live-readers-widget', ['post' => $post])
@endcomponent
```

## 6. Alternative: Minimal Header Badge

### Compact Live Indicator

```blade
<!-- In post header -->
<div class="flex items-center gap-2 mb-4">
    @php
        $breakdown = $post->getReaderBreakdown();
    @endphp
    @if($breakdown['total'] > 0)
        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-semibold">
            <span class="animate-pulse text-lg">👁️</span>
            {{ $breakdown['total'] }} reading •
            {{ $breakdown['authenticated'] }} members •
            {{ $breakdown['anonymous'] }} guests
        </span>
    @endif
</div>
```

## 7. Real-Time Map in Modal

### Popup Map Display

```blade
<!-- Button to open map -->
<button class="btn btn-secondary" onclick="document.getElementById('reader-map-modal').showModal()">
    🗺️ Show Reader Map
</button>

<!-- Modal -->
<dialog id="reader-map-modal" class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
        <h3 class="font-bold text-lg mb-4">Readers from Around the World</h3>
        @livewire('reader-map', ['post' => $post])
        <div class="modal-action">
            <form method="dialog">
                <button class="btn">Close</button>
            </form>
        </div>
    </div>
</dialog>
```

## 8. Email Notification

### Alert When Reader Count Reaches Milestone

```php
<?php

namespace App\Jobs;

use App\Models\Post;
use App\Notifications\ReaderMilestoneReached;

class CheckReaderMilestones
{
    public function handle()
    {
        $posts = Post::all();

        foreach ($posts as $post) {
            $liveCount = $post->getLiveReaderCount();

            // Notify author at milestones: 10, 25, 50, 100
            $milestones = [10, 25, 50, 100];

            foreach ($milestones as $milestone) {
                if ($liveCount === $milestone) {
                    $post->author->notify(new ReaderMilestoneReached($post, $liveCount));
                }
            }
        }
    }
}
```

## 9. Cache Recent Reader Analytics

### Pre-cache for Performance

```php
<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ReaderTrackingService;

class CacheReaderAnalytics extends Command
{
    protected $signature = 'readers:cache-analytics';

    public function handle()
    {
        $service = app(ReaderTrackingService::class);

        Post::published()->each(function ($post) use ($service) {
            // Generate and cache analytics
            $service->generateDailyAnalytics($post->id);
            $service->getReaderMapData($post->id);
            $service->getTopCountries($post->id);

            $this->line("Cached analytics for: {$post->title}");
        });

        $this->info('Analytics cached successfully!');
    }
}
```

## 10. Full-Page Example

### Complete Post Show Page with All Features

```blade
@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->excerpt)

@section('content')
<article class="max-w-5xl mx-auto">
    <!-- Hero Section with Live Readers -->
    <header class="mb-12">
        <!-- Live Reader Badge -->
        <div class="mb-6">
            @php
                $breakdown = $post->getReaderBreakdown();
                $topCountries = $post->getTopCountries(3);
            @endphp
            @if($breakdown['total'] > 0)
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-100 to-blue-50 dark:from-blue-900 dark:to-blue-800 text-blue-900 dark:text-blue-100 rounded-full font-semibold">
                        <span class="animate-pulse text-xl">🔴</span>
                        <span>{{ $breakdown['total'] }} people reading now</span>
                    </span>

                    @foreach($topCountries as $country)
                        <span class="px-3 py-1 bg-gray-100 dark:bg-slate-700 rounded-full text-sm">
                            {{ $country['flag'] }} {{ $country['country'] }} ({{ $country['readers'] }})
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <h1 class="text-5xl font-bold mb-6">{{ $post->title }}</h1>

        <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-4">
                <img src="{{ $post->author->getFirstMediaUrl('avatars') }}" alt="{{ $post->author->name }}" class="w-12 h-12 rounded-full">
                <div>
                    <p class="font-medium">{{ $post->author->name }}</p>
                    <p class="text-sm">{{ $post->published_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Left: Post Content (2/3) -->
        <div class="lg:col-span-2">
            <div class="prose dark:prose-invert max-w-none mb-12">
                {!! $post->content !!}
            </div>

            <!-- Interactive Elements -->
            <div class="bg-gray-50 dark:bg-slate-800 rounded-lg p-8">
                @livewire('reader-geographics', ['post' => $post])
            </div>
        </div>

        <!-- Right: Sidebar (1/3) -->
        <aside class="space-y-6">
            <!-- Live Readers Widget -->
            @livewire('live-readers', ['post' => $post])

            <!-- Author Info Card -->
            <div class="bg-white dark:bg-slate-800 rounded-lg p-6 border border-gray-200 dark:border-slate-700">
                <h3 class="font-bold mb-3">About Author</h3>
                <img src="{{ $post->author->getFirstMediaUrl('avatars') }}" alt="{{ $post->author->name }}" class="w-16 h-16 rounded-full mb-3">
                <p class="font-medium">{{ $post->author->name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $post->author->bio }}</p>
                <button class="btn btn-primary btn-sm">Follow</button>
            </div>
        </aside>
    </div>

    <!-- Global Map Section -->
    <section class="border-t pt-12 mt-12">
        <h2 class="text-3xl font-bold mb-8">🗺️ Global Readers</h2>
        @livewire('reader-map', ['post' => $post])
    </section>
</article>

<!-- Activity Tracking Script -->
<script>
    const postId = {{ $post->id }};

    // Track every 30 seconds
    setInterval(trackActivity, 30000);

    // Track on scroll
    let scrollTimeout;
    document.addEventListener('scroll', () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(trackActivity, 2000);
    });

    // Track on focus
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) trackActivity();
    });

    function trackActivity() {
        fetch(`/api/posts/${postId}/readers/activity`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
    }
</script>
@endsection
```

---

## Quick Copy-Paste Snippets

### Minimal Integration
```blade
@livewire('live-readers', ['post' => $post])
```

### With Map
```blade
@livewire('reader-map', ['post' => $post])
@livewire('reader-geographics', ['post' => $post])
```

### In Controller
```php
$count = $post->getLiveReaderCount();
$breakdown = $post->getReaderBreakdown();
$countries = $post->getTopCountries(5);
```

---

This completes the integration examples! Choose the implementation that best fits your design and use case.
