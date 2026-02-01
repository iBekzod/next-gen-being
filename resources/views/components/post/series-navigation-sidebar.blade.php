@props(['post'])

@if($post->isPartOfSeries())
@php
    $seriesPosts = \App\Models\Post::published()
        ->inSeries($post->series_slug)
        ->with(['author', 'category'])
        ->orderBy('series_part')
        ->get();
    $currentIndex = $seriesPosts->search(fn($p) => $p->id === $post->id);

    // Get user's progress in this series if authenticated
    $completedPostIds = [];
    $readPostIds = [];
    if (Auth::check()) {
        $tutorialProgressService = app(\App\Services\Tutorial\TutorialProgressService::class);
        $seriesProgress = $tutorialProgressService->getSeriesProgress(Auth::user(), $post->series_slug);
        $completedCount = $seriesProgress['completed'] ?? 0;
        $totalParts = $seriesProgress['total'] ?? $post->series_total_parts;
        $progress = $seriesProgress['percentage'] ?? 0;

        // Get IDs of completed and read posts
        $progressRecords = \App\Models\TutorialProgress::where('user_id', Auth::id())
            ->where('series_slug', $post->series_slug)
            ->get();

        $completedPostIds = $progressRecords->where('completed', true)->pluck('post_id')->toArray();
        $readPostIds = $progressRecords->where('read_count', '>', 0)->pluck('post_id')->toArray();
    } else {
        // For unauthenticated users, show post order progression only
        $completedCount = $currentIndex !== false ? $currentIndex : 0;
        $totalParts = $post->series_total_parts;
        $progress = round(($completedCount / $totalParts) * 100);
    }
@endphp

<!-- Sticky Sidebar -->
<div class="sticky top-4">
    <div class="overflow-hidden border-2 border-blue-500/30 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 shadow-lg">
        <!-- Series Header -->
        <div class="px-4 py-3 border-b border-blue-500/20 bg-gradient-to-r from-blue-500 to-indigo-600">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                </svg>
                <span class="text-xs font-semibold tracking-wide text-white uppercase">Tutorial Series</span>
            </div>
            <h3 class="text-base font-bold text-white line-clamp-2">{{ $post->series_title }}</h3>

            <!-- Progress Info -->
            <div class="flex items-center justify-between mt-3">
                <div class="text-white">
                    <div class="text-lg font-bold">{{ $completedCount }}/{{ $totalParts }}</div>
                    <div class="text-[10px] text-blue-100">Parts Complete</div>
                </div>
                <div class="text-right text-white">
                    <div class="text-lg font-bold">{{ $progress }}%</div>
                    <div class="text-[10px] text-blue-100">Progress</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-3">
                <div class="h-2 overflow-hidden rounded-full bg-white/20">
                    <div class="h-full transition-all duration-500 bg-gradient-to-r from-green-400 to-emerald-500" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </div>

        <!-- Series Parts List (Scrollable) -->
        <div class="overflow-y-auto" style="max-height: calc(100vh - 300px);">
            <div class="p-3 space-y-1.5">
                @foreach($seriesPosts as $index => $seriesPost)
                @php
                    $isCurrent = $seriesPost->id === $post->id;
                    // Check if post is completed or read by user
                    $isCompleted = Auth::check() ? in_array($seriesPost->id, $completedPostIds) : false;
                    $isRead = Auth::check() ? in_array($seriesPost->id, $readPostIds) : false;
                    $isLocked = !$isCurrent && !$isCompleted && $seriesPost->is_premium && !auth()->check();
                @endphp

                <a href="{{ $isLocked ? '#' : route('posts.show', $seriesPost->slug) }}"
                   class="flex items-start gap-3 p-3 transition-all duration-200 rounded-lg group {{ $isCurrent ? 'bg-blue-500 text-white shadow-md' : 'bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700' }} {{ $isLocked ? 'opacity-60 cursor-not-allowed' : '' }}">

                    <!-- Part Number Circle -->
                    <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 text-xs font-bold rounded-full {{ $isCurrent ? 'bg-white text-blue-500' : ($isCompleted ? 'bg-green-500 text-white' : ($isRead ? 'bg-blue-400 text-white' : 'bg-gray-200 dark:bg-slate-700 text-gray-600 dark:text-gray-300')) }}">
                        @if($isCompleted)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($isRead)
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1 4.5 4.5 0 11-4.384 5.98z"/>
                            </svg>
                        @else
                            {{ $seriesPost->series_part }}
                        @endif
                    </div>

                    <!-- Part Info -->
                    <div class="flex-1 min-w-0">
                        @if($isCurrent)
                            <span class="inline-block px-1.5 py-0.5 mb-1 text-[10px] font-bold text-blue-500 bg-white rounded">You are here</span>
                        @endif
                        <h4 class="text-xs font-semibold {{ $isCurrent ? 'text-white' : 'text-gray-900 dark:text-gray-100' }} line-clamp-2 leading-snug">
                            {{ $seriesPost->title }}
                        </h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] {{ $isCurrent ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $seriesPost->read_time }} min
                            </span>
                            @if($seriesPost->is_premium)
                            <span class="inline-flex items-center gap-0.5 px-1 py-0.5 text-[10px] font-medium text-yellow-700 bg-yellow-100 rounded dark:bg-yellow-900/30 dark:text-yellow-400">
                                <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Premium
                            </span>
                            @endif
                            @if($isLocked)
                            <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Arrow Icon (only for non-current) -->
                    @if(!$isCurrent && !$isLocked)
                    <div class="flex-shrink-0 mt-1 transition-transform group-hover:translate-x-0.5">
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    @endif
                </a>
                @endforeach
            </div>
        </div>

        <!-- Series Navigation Footer -->
        <div class="flex items-center gap-2 px-3 py-3 border-t border-blue-500/20 bg-gray-50 dark:bg-slate-800/50">
            @if($post->previousInSeries())
            <a href="{{ route('posts.show', $post->previousInSeries()->slug) }}"
               class="flex items-center justify-center flex-1 gap-1.5 px-3 py-2 text-xs font-medium text-blue-600 transition-colors bg-white border border-blue-500 rounded-lg hover:bg-blue-50 dark:bg-slate-700 dark:text-blue-400 dark:hover:bg-slate-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Previous
            </a>
            @else
            <div class="flex-1"></div>
            @endif

            @if($post->nextInSeries())
            <a href="{{ route('posts.show', $post->nextInSeries()->slug) }}"
               class="flex items-center justify-center flex-1 gap-1.5 px-3 py-2 text-xs font-medium text-white transition-all bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg hover:shadow-md">
                Next
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @else
            <div class="flex items-center justify-center flex-1 gap-1.5 px-3 py-2 text-xs font-medium text-green-600 bg-green-100 border border-green-500 rounded-lg dark:bg-green-900/30 dark:text-green-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Complete!
            </div>
            @endif
        </div>

        <!-- View All Series Link -->
        <div class="px-3 py-2 text-center border-t border-blue-500/20">
            <a href="{{ route('tutorials.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                View All Tutorial Series →
            </a>
        </div>
    </div>
</div>
@endif
