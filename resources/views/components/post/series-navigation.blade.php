@props(['post'])

@if($post->isPartOfSeries())
@php
    $seriesPosts = \App\Models\Post::published()
        ->inSeries($post->series_slug)
        ->with(['author', 'category'])
        ->get();
    $currentIndex = $seriesPosts->search(fn($p) => $p->id === $post->id);
    $progress = $post->getSeriesProgress();
@endphp

<div class="mb-8 overflow-hidden border-2 border-blue-500/30 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900">
    <!-- Series Header -->
    <div class="px-6 py-4 border-b border-blue-500/20 bg-gradient-to-r from-blue-500 to-indigo-600">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                    </svg>
                    <span class="text-xs font-semibold tracking-wide text-white uppercase">Tutorial Series</span>
                </div>
                <h3 class="text-lg font-bold text-white">{{ $post->series_title }}</h3>
                @if($post->series_description)
                <p class="mt-1 text-sm text-blue-100">{{ Str::limit($post->series_description, 120) }}</p>
                @endif
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-white">{{ $post->series_part }}/{{ $post->series_total_parts }}</div>
                <div class="text-xs text-blue-100">Parts</div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mt-4">
            <div class="flex items-center justify-between mb-1 text-xs text-blue-100">
                <span>Your Progress</span>
                <span>{{ $progress }}% Complete</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-white/20">
                <div class="h-full transition-all duration-500 bg-gradient-to-r from-green-400 to-emerald-500" style="width: {{ $progress }}%"></div>
            </div>
        </div>
    </div>

    <!-- Series Parts List -->
    <div class="p-4">
        <div class="space-y-2">
            @foreach($seriesPosts as $index => $seriesPost)
            @php
                $isCurrent = $seriesPost->id === $post->id;
                $isCompleted = $index < $currentIndex;
                $isLocked = !$isCurrent && !$isCompleted && $seriesPost->is_premium && !auth()->check();
            @endphp

            <div class="relative group">
                <a href="{{ $isLocked ? '#' : route('posts.show', $seriesPost->slug) }}"
                   class="flex items-center gap-4 p-4 transition-all duration-200 rounded-lg {{ $isCurrent ? 'bg-blue-500 text-white shadow-lg scale-105' : 'bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700' }} {{ $isLocked ? 'opacity-60 cursor-not-allowed' : '' }}">

                    <!-- Part Number Circle -->
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 rounded-full {{ $isCurrent ? 'bg-white text-blue-500' : ($isCompleted ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-slate-700 text-gray-600 dark:text-gray-300') }}">
                        @if($isCompleted)
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <span class="text-lg font-bold">{{ $seriesPost->series_part }}</span>
                        @endif
                    </div>

                    <!-- Part Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-semibold {{ $isCurrent ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400' }}">
                                Part {{ $seriesPost->series_part }}
                            </span>
                            @if($isCurrent)
                                <span class="px-2 py-0.5 text-xs font-bold text-blue-500 bg-white rounded-full">You are here</span>
                            @endif
                            @if($seriesPost->is_premium)
                                <svg class="w-4 h-4 {{ $isCurrent ? 'text-yellow-300' : 'text-yellow-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endif
                        </div>
                        <h4 class="text-sm font-semibold {{ $isCurrent ? 'text-white' : 'text-gray-900 dark:text-gray-100' }} line-clamp-2 group-hover:text-blue-600">
                            {{ $seriesPost->title }}
                        </h4>
                        <p class="mt-1 text-xs {{ $isCurrent ? 'text-blue-100' : 'text-gray-600 dark:text-gray-400' }} line-clamp-1">
                            {{ $seriesPost->excerpt }}
                        </p>
                        <div class="flex items-center gap-3 mt-2 text-xs {{ $isCurrent ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400' }}">
                            <span>{{ $seriesPost->read_time }} min read</span>
                            <span>•</span>
                            <span>{{ $seriesPost->published_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <!-- Lock Icon for Premium -->
                    @if($isLocked)
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    @endif

                    <!-- Arrow for Current/Accessible -->
                    @if(!$isLocked && !$isCurrent)
                    <div class="flex-shrink-0 transition-transform group-hover:translate-x-1">
                        <svg class="w-5 h-5 {{ $isCurrent ? 'text-white' : 'text-gray-400 dark:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Series Navigation Footer -->
    <div class="flex items-center justify-between px-6 py-4 border-t border-blue-500/20 bg-gray-50 dark:bg-slate-800/50">
        @if($post->previousInSeries())
        <a href="{{ route('posts.show', $post->previousInSeries()->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 transition-colors bg-white border-2 border-blue-500 rounded-lg hover:bg-blue-50 dark:bg-slate-700 dark:text-blue-400 dark:hover:bg-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Previous Part
        </a>
        @else
        <div></div>
        @endif

        @if($post->nextInSeries())
        <a href="{{ route('posts.show', $post->nextInSeries()->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-all bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg hover:shadow-lg hover:scale-105">
            Next Part
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @else
        <div class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-600 bg-green-100 border-2 border-green-500 rounded-lg dark:bg-green-900/30 dark:text-green-400">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Series Complete!
        </div>
        @endif
    </div>
</div>
@endif
