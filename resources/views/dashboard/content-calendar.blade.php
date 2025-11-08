@extends('layouts.app')

@section('title', 'Content Calendar - Dashboard')
@section('description', 'Plan your content with a visual calendar, manage series, and schedule posts')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-emerald-500/20 text-emerald-200">📅 Calendar</span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Content Calendar</h1>
        <p class="mt-4 text-base text-slate-300">Plan your publishing schedule, manage series, and view your content roadmap.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl space-y-12">
        <!-- Content Statistics -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Published</p>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $publishedCount }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Scheduled</p>
                <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $scheduledCount }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Drafts</p>
                <p class="mt-2 text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $draftCount }}</p>
            </div>
        </div>

        <!-- Upcoming Schedule (30 days) -->
        @if($upcomingPosts->isNotEmpty())
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming (Next 30 Days)</h2>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-slate-700">
                @foreach($upcomingPosts as $post)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $post->title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $post->published_at->format('M d, Y H:i') }}
                            @if($post->series_title)
                            • Series: {{ $post->series_title }} ({{ $post->series_part }}/{{ $post->series_total_parts }})
                            @endif
                        </p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($post->status === 'scheduled')
                            bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300
                        @elseif($post->status === 'draft')
                            bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300
                        @else
                            bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                        @endif
                    ">
                        {{ ucfirst($post->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Series Management -->
        @if($series->isNotEmpty())
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Content Series</h2>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-slate-700">
                @foreach($series as $s)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $s->series_title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $s->post_count }} of {{ $s->total_parts ?? '?' }} posts
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format(($s->post_count / ($s->total_parts ?? 1)) * 100, 0) }}%
                            </p>
                        </div>
                    </div>
                    <div class="mt-2 w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-600 transition-all"
                             style="width: {{ ($s->post_count / ($s->total_parts ?? 1)) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Scheduled Posts -->
        @if($scheduledPosts->isNotEmpty())
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Scheduled Posts</h2>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-slate-700">
                @foreach($scheduledPosts as $post)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $post->title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Scheduled for {{ $post->published_at->format('M d, Y \a\t H:i') }}
                            </p>
                            @if($post->series_title)
                            <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                                📚 Series: {{ $post->series_title }} (Part {{ $post->series_part }})
                            </p>
                            @endif
                        </div>
                        <a href="{{ route('posts.edit', $post->slug) }}" class="px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                            Edit
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Draft Posts -->
        @if($draftPosts->isNotEmpty())
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Draft Posts</h2>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-slate-700">
                @foreach($draftPosts as $post)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $post->title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Started {{ $post->created_at->diffForHumans() }}
                            </p>
                            @if($post->series_title)
                            <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                                📚 Series: {{ $post->series_title }}
                            </p>
                            @endif
                        </div>
                        <a href="{{ route('posts.edit', $post->slug) }}" class="px-3 py-2 text-sm font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/40 transition">
                            Continue
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Create New Post CTA -->
        <div class="rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 shadow-lg p-8 text-white text-center">
            <h3 class="text-2xl font-bold mb-2">Ready to Create?</h3>
            <p class="text-emerald-100 mb-6">Start writing your next post or schedule content for future publishing</p>
            <a href="{{ route('posts.create') }}" class="inline-flex items-center px-6 py-3 bg-white text-emerald-600 font-semibold rounded-lg hover:bg-emerald-50 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Post
            </a>
        </div>
    </div>
</section>
@endsection
