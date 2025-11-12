@extends('layouts.app')

@section('title', 'Tutorial Series - ' . setting('site_name'))
@section('description', 'Browse our comprehensive tutorial series covering web development, programming, and technology')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl"></div>
    </div>

    <div class="relative px-6 py-20 mx-auto max-w-7xl">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 mb-6 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200 border border-blue-500/30">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                </svg>
                Learn & Master
            </div>
            <h1 class="text-5xl sm:text-6xl font-bold tracking-tight mb-6">Learning Paths for Developers</h1>
            <p class="text-xl text-slate-300 leading-relaxed">Comprehensive tutorial series designed to take you from beginner to expert. Learn modern technologies with hands-on examples and real-world projects.</p>
        </div>

        <!-- Search Bar -->
        <div class="max-w-2xl mx-auto">
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg blur opacity-20 group-hover:opacity-100 transition duration-1000"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-lg p-1">
                    <div class="flex items-center gap-3 px-4 py-3 text-gray-400 dark:text-gray-500">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text"
                               id="tutorialSearch"
                               placeholder="Search tutorials..."
                               class="flex-1 bg-transparent text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 outline-none text-sm">
                    </div>
                </div>
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

<!-- Statistics Bar -->
<section class="bg-slate-900 border-b border-slate-800">
    <div class="px-6 py-8 mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-3 md:grid-cols-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-white">{{ count($series) }}</p>
                <p class="mt-1 text-sm text-gray-400">Tutorial Series</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-blue-400">{{ count($series->where('is_complete', true)) }}</p>
                <p class="mt-1 text-sm text-gray-400">Completed</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-emerald-400">{{ collect($series)->sum('published_parts') }}</p>
                <p class="mt-1 text-sm text-gray-400">Total Parts</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-amber-400">Beginner+</p>
                <p class="mt-1 text-sm text-gray-400">All Levels</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-16 bg-gray-50 dark:bg-slate-900">
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
            <!-- Tutorial Series Grid - Card Layout -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($series as $item)
                <div class="group relative bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 hover:border-blue-500 dark:hover:border-blue-400 h-full flex flex-col">

                    <!-- Featured Image -->
                    <div class="relative w-full h-48 overflow-hidden bg-gradient-to-br from-blue-500 to-purple-600">
                        @if($item['featured_image'])
                            <img src="{{ $item['featured_image'] }}"
                                 alt="{{ $item['title'] }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-5xl">📚</div>
                        @endif

                        <!-- Status Badge Overlay -->
                        <div class="absolute top-4 right-4">
                            @if($item['is_complete'])
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 dark:bg-emerald-500 rounded-full shadow-lg backdrop-blur-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Complete
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-blue-600 dark:bg-blue-500 rounded-full shadow-lg backdrop-blur-sm">
                                <span>◉</span>
                                In Progress
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Card Content -->
                    <div class="p-6 flex-1 flex flex-col">
                        <!-- Title -->
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {{ $item['title'] }}
                        </h3>

                        <!-- Description -->
                        @if($item['description'])
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2 flex-1">
                            {{ $item['description'] }}
                        </p>
                        @endif

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Progress</span>
                                <span class="text-xs font-bold {{ $item['is_complete'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400' }}">
                                    {{ $item['published_parts'] }}/{{ $item['total_parts'] }} parts
                                </span>
                            </div>
                            <div class="w-full h-2.5 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full transition-all {{ $item['is_complete'] ? 'bg-gradient-to-r from-emerald-400 to-emerald-600' : 'bg-gradient-to-r from-blue-400 to-blue-600' }}"
                                     style="width: {{ $item['total_parts'] > 0 ? ($item['published_parts'] / $item['total_parts']) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        <!-- Last Updated -->
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-4 pb-4 border-b border-gray-200 dark:border-slate-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Updated {{ $item['last_updated']->diffForHumans() }}</span>
                        </div>

                        <!-- View Series Button -->
                        <a href="{{ route('tutorials.series', $item['slug']) }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 hover:from-blue-700 hover:to-blue-800 dark:hover:from-blue-800 dark:hover:to-blue-900 text-white font-bold text-sm rounded-lg shadow-md hover:shadow-lg transition-all duration-300 w-full">
                            <span>View Series</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
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
