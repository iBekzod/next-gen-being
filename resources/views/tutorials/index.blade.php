@extends('layouts.app')

@section('title', 'Tutorial Series - ' . setting('site_name'))
@section('description', 'Browse our comprehensive tutorial series covering web development, programming, and technology')

@section('content')
<!-- Header Section -->
<section class="relative bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl"></div>
    </div>

    <div class="relative px-6 py-12 mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-4xl font-bold tracking-tight mb-2">Tutorials</h1>
            <p class="text-gray-300">Comprehensive tutorial series to master modern technologies</p>
        </div>

        <!-- Simple Stats Header -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 text-sm">
            <div>
                <p class="text-2xl font-bold text-white">{{ count($series) }}</p>
                <p class="text-gray-400">Tutorial Series</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-blue-400">{{ count($series->where('is_complete', true)) }}</p>
                <p class="text-gray-400">Completed</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-emerald-400">{{ collect($series)->sum('published_parts') }}</p>
                <p class="text-gray-400">Total Parts</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-amber-400">Beginner+</p>
                <p class="text-gray-400">All Levels</p>
            </div>
        </div>
    </div>
</section>

<!-- User Progress Section (if logged in) -->
@auth
<section class="py-12 bg-slate-900 border-b border-slate-800">
    <div class="px-6 mx-auto max-w-7xl">
        <x-learning-progress-card />
    </div>
</section>
@endauth

<!-- Main Content -->
<section class="py-12 bg-gray-50 dark:bg-slate-900">
    <div class="px-6 mx-auto max-w-7xl">
        @if($series->isEmpty())
            <!-- Empty State -->
            <x-empty-state-advanced
                icon="📚"
                title="No Tutorial Series Yet"
                description="Our tutorial library is being built. Check back soon for comprehensive learning paths!"
                size="lg"
            />
        @else
            <!-- Tutorial Series List -->
            <div class="space-y-8">
                @foreach($series as $item)
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden shadow-md hover:shadow-lg transition-all" x-data="{ open: false }">
                    <!-- Series Header with Image -->
                    <div class="flex flex-col sm:flex-row gap-6 p-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors" @click="open = !open">
                        <!-- Featured Image -->
                        <div class="flex-shrink-0 w-full sm:w-40 h-40 rounded-lg overflow-hidden bg-gradient-to-br from-blue-500 to-purple-600">
                            @if($item['featured_image'])
                                <img src="{{ $item['featured_image'] }}"
                                     alt="{{ $item['title'] }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-4xl">📚</div>
                            @endif
                        </div>

                        <!-- Series Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $item['title'] }}</h3>
                                    @if($item['description'])
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ $item['description'] }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($item['is_complete'])
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-white bg-emerald-600 dark:bg-emerald-500 rounded-full whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Complete
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-white bg-blue-600 dark:bg-blue-500 rounded-full whitespace-nowrap">
                                        <span>◉</span> In Progress
                                    </span>
                                    @endif
                                    <!-- Expand/Collapse Chevron -->
                                    <svg class="flex-shrink-0 w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                                         :class="{ 'rotate-180': open }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Progress -->
                            <div class="mt-3 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600 dark:text-gray-400 font-medium">{{ $item['published_parts'] }}/{{ $item['total_parts'] }} parts</span>
                                    <span class="text-gray-500 dark:text-gray-400">Updated {{ $item['last_updated']->diffForHumans() }}</span>
                                </div>
                                <div class="w-full h-2 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full transition-all {{ $item['is_complete'] ? 'bg-gradient-to-r from-emerald-400 to-emerald-600' : 'bg-gradient-to-r from-blue-400 to-blue-600' }}"
                                         style="width: {{ $item['total_parts'] > 0 ? ($item['published_parts'] / $item['total_parts']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Series Parts List -->
                    <div class="border-t border-gray-200 dark:border-slate-700" x-show="open" x-transition>
                        @php
                            $seriesPosts = \App\Models\Post::published()
                                ->inSeries($item['slug'])
                                ->orderBy('series_part')
                                ->get();
                        @endphp

                        @if($seriesPosts->isNotEmpty())
                            <div class="divide-y divide-gray-100 dark:divide-slate-700">
                                @foreach($seriesPosts as $post)
                                <a href="{{ route('posts.show', $post->slug) }}"
                                   class="flex items-center gap-4 p-4 hover:bg-blue-50 dark:hover:bg-slate-700/50 transition-colors group">
                                    <!-- Part Number -->
                                    <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white font-bold text-sm">
                                        {{ str_pad($post->series_part, 2, '0', STR_PAD_LEFT) }}
                                    </div>

                                    <!-- Post Info -->
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate">
                                            {{ $post->title }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-600 dark:text-gray-400">
                                            <span>{{ $post->read_time }} min read</span>
                                            @if($post->is_premium)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded font-bold text-xs">
                                                ⭐ Premium
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Arrow -->
                                    <svg class="flex-shrink-0 w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-blue-500 transition-all group-hover:translate-x-1"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">No parts published yet</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<!-- Leaderboard Section -->
<section class="py-16 bg-gray-50 dark:bg-slate-900 border-t border-gray-200 dark:border-slate-800">
    <div class="px-6 mx-auto max-w-7xl">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Learning Leaderboard</h2>
            <p class="text-gray-600 dark:text-gray-400">See who's making the most progress in the community</p>
        </div>
        <x-leaderboard :limit="10" />
    </div>
</section>

<!-- Tips Section -->
<section class="py-16 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 border-t border-blue-200 dark:border-slate-700">
    <div class="px-6 mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <div class="rounded-xl bg-white dark:bg-slate-800/50 p-6 border border-blue-100 dark:border-slate-700">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Learn by Doing</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Each tutorial includes hands-on examples and real-world projects to reinforce your learning.</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800/50 p-6 border border-blue-100 dark:border-slate-700">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Progressive Learning</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Start from the basics and gradually progress to advanced topics at your own pace.</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800/50 p-6 border border-blue-100 dark:border-slate-700">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Free & Open</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">All tutorials are free to access. Premium content is available for subscribers.</p>
            </div>
        </div>
    </div>
</section>

@endsection

<script>
    // Simple search functionality
    document.getElementById('tutorialSearch')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const series = document.querySelectorAll('[data-series-title]');

        series.forEach(item => {
            const title = item.getAttribute('data-series-title').toLowerCase();
            item.style.display = title.includes(searchTerm) ? '' : 'none';
        });
    });
</script>
