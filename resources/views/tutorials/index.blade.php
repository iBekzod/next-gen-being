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
                <div class="relative bg-slate-800 rounded-lg p-1">
                    <div class="flex items-center gap-3 px-4 py-3 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text"
                               id="tutorialSearch"
                               placeholder="Search tutorials..."
                               class="flex-1 bg-transparent text-white placeholder-gray-500 outline-none text-sm">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
            <!-- Tutorial Series Grid -->
            <div class="space-y-4">
                @foreach($series as $item)
                <div x-data="{ open: false }"
                     data-series-title="{{ $item['title'] }}"
                     class="group relative bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">

                    <!-- Series Card Header -->
                    <button @click="open = !open" class="w-full text-left">
                        <div class="px-6 py-6 flex items-center justify-between">
                            <!-- Left Content -->
                            <div class="flex items-center gap-5 flex-1 min-w-0">
                                <!-- Gradient Icon -->
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Series Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ $item['title'] }}
                                        </h3>
                                        @if($item['is_complete'])
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/30 rounded-full border border-emerald-200 dark:border-emerald-800">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Complete
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/30 rounded-full border border-blue-200 dark:border-blue-800">
                                            In Progress
                                        </span>
                                        @endif
                                    </div>
                                    @if($item['description'])
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $item['description'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Right Stats -->
                            <div class="flex-shrink-0 ml-6 flex items-center gap-6">
                                <!-- Progress Stats -->
                                <div class="hidden sm:flex flex-col items-end gap-2">
                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $item['published_parts'] }}<span class="text-sm text-gray-500 dark:text-gray-400 font-normal">/{{ $item['total_parts'] }}</span></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Parts</p>
                                    </div>
                                    <div class="w-24 h-2 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full transition-all {{ $item['is_complete'] ? 'bg-gradient-to-r from-emerald-400 to-emerald-600' : 'bg-gradient-to-r from-blue-400 to-blue-600' }}"
                                             style="width: {{ ($item['published_parts'] / $item['total_parts']) * 100 }}%"></div>
                                    </div>
                                </div>

                                <!-- Expand Icon -->
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-gray-400 dark:text-gray-500 transition-transform duration-300"
                                         :class="{ 'rotate-180': open }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </button>

                    <!-- Expandable Content -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
                        <div class="px-6 py-6">
                            @php
                                $seriesPosts = \App\Models\Post::published()
                                    ->inSeries($item['slug'])
                                    ->orderBy('series_part')
                                    ->get();
                            @endphp

                            @if($seriesPosts->isEmpty())
                                <p class="text-center py-8 text-gray-500 dark:text-gray-400">No parts published yet</p>
                            @else
                                <div class="space-y-2">
                                    @foreach($seriesPosts as $post)
                                    <a href="{{ route('posts.show', $post->slug) }}"
                                       class="flex items-center gap-4 p-4 rounded-lg border border-transparent hover:border-blue-200 dark:hover:border-blue-800 hover:bg-white dark:hover:bg-slate-800 transition-all duration-200 group/item">

                                        <!-- Part Badge -->
                                        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 text-white font-bold text-sm group-hover/item:shadow-lg transition-shadow">
                                            {{ str_pad($post->series_part, 2, '0', STR_PAD_LEFT) }}
                                        </div>

                                        <!-- Part Content -->
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-gray-900 dark:text-white group-hover/item:text-blue-600 dark:group-hover/item:text-blue-400 transition-colors truncate">
                                                {{ $post->title }}
                                            </h4>
                                            <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ $post->read_time }} min
                                                </span>
                                                @if($post->is_premium)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full font-medium">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                    Premium
                                                </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Arrow -->
                                        <svg class="flex-shrink-0 w-5 h-5 text-gray-300 dark:text-gray-600 group-hover/item:text-blue-500 transition-all group-hover/item:translate-x-1"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
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
