@extends('layouts.app')

@section('title', $seriesInfo['title'] . ' - Tutorial Series - ' . setting('site_name'))
@section('description', $seriesInfo['description'] ?? 'A comprehensive tutorial series')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <a href="{{ route('tutorials.index') }}" class="inline-flex items-center gap-2 mb-6 text-xs font-semibold tracking-wide uppercase transition-colors text-blue-200 hover:text-white">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to all tutorials
        </a>

        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 mb-4 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                </svg>
                {{ $posts->count() }}/{{ $seriesInfo['total_parts'] }} Parts
            </div>
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ $seriesInfo['title'] }}</h1>
            @if($seriesInfo['description'])
            <p class="mt-4 text-base text-slate-300">{{ $seriesInfo['description'] }}</p>
            @endif

            <!-- Progress -->
            <div class="mt-6">
                <div class="flex items-center justify-between mb-2 text-sm text-slate-300">
                    <span>Your Progress</span>
                    <span>{{ round(($posts->count() / $seriesInfo['total_parts']) * 100) }}% Complete</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-white/20">
                    <div class="h-full transition-all duration-500 bg-gradient-to-r from-green-400 to-emerald-500"
                         style="width: {{ ($posts->count() / $seriesInfo['total_parts']) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-12 bg-gray-50 dark:bg-slate-900">
    <div class="px-4 mx-auto max-w-6xl sm:px-6 lg:px-8">
        <div class="space-y-6">
            @foreach($posts as $post)
            <article class="overflow-hidden transition-all duration-300 bg-white border-2 border-gray-300 shadow-md dark:bg-slate-800 dark:border-slate-700 group rounded-xl hover:shadow-xl hover:border-blue-500 dark:hover:border-blue-500 hover:-translate-y-1">
                <div class="flex flex-col md:flex-row">
                    <!-- Featured Image -->
                    <div class="relative md:w-80 h-48 md:h-auto overflow-hidden bg-gradient-to-br from-blue-500 to-purple-600">
                        @if($post->featured_image)
                            <img src="{{ $post->featured_image }}"
                                 alt="{{ $post->title }}"
                                 class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                        @else
                            <div class="flex items-center justify-center w-full h-full">
                                <svg class="w-16 h-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        @endif

                        <!-- Part Number Badge -->
                        <div class="absolute top-3 left-3">
                            <div class="flex items-center justify-center w-12 h-12 text-lg font-bold text-white bg-blue-600 rounded-full shadow-lg">
                                {{ $post->series_part }}
                            </div>
                        </div>

                        @if($post->is_premium)
                        <div class="absolute top-3 right-3">
                            <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-white rounded-full bg-gradient-to-r from-yellow-400 to-orange-500">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Premium
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-1 p-6">
                        <!-- Category & Reading Time -->
                        <div class="flex items-center gap-3 mb-3">
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-{{ $post->category->color ?? 'blue' }}-100 text-{{ $post->category->color ?? 'blue' }}-800">
                                {{ $post->category->name }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $post->read_time }} min read</span>
                        </div>

                        <!-- Title -->
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">
                            <a href="{{ route('posts.show', $post->slug) }}">
                                {{ $post->title }}
                            </a>
                        </h3>

                        <!-- Excerpt -->
                        <p class="mb-4 text-gray-600 dark:text-gray-400 line-clamp-2">
                            {{ $post->excerpt }}
                        </p>

                        <!-- Meta -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $post->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) }}"
                                     alt="{{ $post->author->name }}"
                                     class="w-8 h-8 rounded-full">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $post->author->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $post->published_at->format('M j, Y') }}</p>
                                </div>
                            </div>

                            <a href="{{ route('posts.show', $post->slug) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-all transform bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:shadow-lg hover:scale-105">
                                Read Part {{ $post->series_part }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Tags -->
                        @if($post->tags->count() > 0)
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($post->tags->take(3) as $tag)
                            <span class="px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded dark:bg-gray-700 dark:text-gray-300">
                                {{ $tag->name }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
