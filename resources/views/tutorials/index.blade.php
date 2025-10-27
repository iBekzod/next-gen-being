@extends('layouts.app')

@section('title', 'Tutorial Series - ' . setting('site_name'))
@section('description', 'Browse our comprehensive tutorial series covering web development, programming, and technology')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 mb-4 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                </svg>
                Tutorial Series
            </div>
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Step-by-Step Learning Paths</h1>
            <p class="mt-4 text-base text-slate-300">Master modern technologies with our comprehensive tutorial series. Each series takes you from beginner to advanced with hands-on examples and real code.</p>
        </div>
    </div>
</section>

<section class="py-12 bg-gray-50 dark:bg-slate-900">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if($series->isEmpty())
            <div class="py-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No tutorial series yet</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Check back soon for new learning content!</p>
            </div>
        @else
            <!-- Notion-style Collapsible Tutorial List -->
            <div class="space-y-2">
                @foreach($series as $item)
                <div x-data="{ open: false }" class="overflow-hidden transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-slate-800 dark:border-slate-700">
                    <!-- Series Header (Collapsible) -->
                    <button @click="open = !open" class="flex items-center justify-between w-full p-4 text-left transition-colors hover:bg-gray-50 dark:hover:bg-slate-700/50">
                        <div class="flex items-center flex-1 gap-4">
                            <!-- Expand/Collapse Icon -->
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-500 transition-transform duration-200 dark:text-gray-400"
                                     :class="{ 'rotate-90': open }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>

                            <!-- Series Icon -->
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Series Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-base font-semibold text-gray-900 truncate dark:text-white">
                                        {{ $item['title'] }}
                                    </h3>
                                    @if($item['is_complete'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Complete
                                    </span>
                                    @endif
                                </div>
                                @if($item['description'])
                                <p class="text-sm text-gray-600 truncate dark:text-gray-400">{{ $item['description'] }}</p>
                                @endif
                            </div>

                            <!-- Series Stats -->
                            <div class="flex items-center gap-6 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>{{ $item['published_parts'] }}/{{ $item['total_parts'] }}</span>
                                </div>
                                <div class="hidden sm:block">
                                    <div class="w-24 h-1.5 bg-gray-200 rounded-full dark:bg-gray-700">
                                        <div class="h-full transition-all {{ $item['is_complete'] ? 'bg-green-500' : 'bg-blue-500' }} rounded-full"
                                             style="width: {{ ($item['published_parts'] / $item['total_parts']) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </button>

                    <!-- Collapsible Content (Parts List) -->
                    <div x-show="open"
                         x-collapse
                         class="border-t border-gray-200 dark:border-slate-700">
                        <div class="p-4 pl-16 space-y-2 bg-gray-50 dark:bg-slate-900/50">
                            @php
                                $seriesPosts = \App\Models\Post::published()
                                    ->inSeries($item['slug'])
                                    ->orderBy('series_part')
                                    ->get();
                            @endphp

                            @if($seriesPosts->isEmpty())
                                <p class="text-sm text-gray-500 dark:text-gray-400">No parts published yet</p>
                            @else
                                @foreach($seriesPosts as $post)
                                <a href="{{ route('posts.show', $post->slug) }}"
                                   class="flex items-center gap-3 p-3 transition-all rounded-lg hover:bg-white dark:hover:bg-slate-800 group">
                                    <!-- Part Number -->
                                    <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 text-sm font-semibold text-white bg-blue-600 rounded-lg">
                                        {{ $post->series_part }}
                                    </div>

                                    <!-- Part Info -->
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 truncate dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                            {{ $post->title }}
                                        </h4>
                                        <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $post->read_time }} min read</span>
                                            @if($post->is_premium)
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-xs font-medium text-yellow-700 bg-yellow-100 rounded dark:bg-yellow-900/30 dark:text-yellow-400">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                                Premium
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Arrow Icon -->
                                    <svg class="flex-shrink-0 w-5 h-5 text-gray-400 transition-transform group-hover:translate-x-1 dark:text-gray-600"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
