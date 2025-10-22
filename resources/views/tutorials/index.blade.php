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
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach($series as $item)
                <article class="overflow-hidden transition-all duration-300 bg-white border-2 border-gray-300 shadow-md dark:bg-slate-800 dark:border-slate-700 group rounded-xl hover:shadow-xl hover:border-blue-500 dark:hover:border-blue-500 hover:-translate-y-1">
                    <a href="{{ route('series.show', $item['slug']) }}" class="block">
                        <!-- Featured Image -->
                        <div class="relative overflow-hidden h-48 bg-gradient-to-br from-blue-500 to-purple-600">
                            @if($item['featured_image'])
                                <img src="{{ $item['featured_image'] }}"
                                     alt="{{ $item['title'] }}"
                                     class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                            @else
                                <div class="flex items-center justify-center w-full h-full">
                                    <svg class="w-16 h-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif

                            <!-- Series Badge -->
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-white rounded-full bg-black/50 backdrop-blur-sm">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                                    </svg>
                                    {{ $item['published_parts'] }}/{{ $item['total_parts'] }} Parts
                                </span>
                            </div>

                            <!-- Complete Badge -->
                            @if($item['is_complete'])
                            <div class="absolute top-3 left-3">
                                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-white bg-green-500 rounded-full">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Complete
                                </span>
                            </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <!-- Category -->
                            @if($item['category'])
                            <span class="inline-block px-3 py-1 mb-3 text-xs font-medium rounded-full bg-{{ $item['category']->color ?? 'blue' }}-100 text-{{ $item['category']->color ?? 'blue' }}-800">
                                {{ $item['category']->name }}
                            </span>
                            @endif

                            <!-- Title -->
                            <h3 class="mb-3 text-xl font-bold text-gray-900 transition-colors dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                {{ $item['title'] }}
                            </h3>

                            <!-- Description -->
                            @if($item['description'])
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                                {{ $item['description'] }}
                            </p>
                            @endif

                            <!-- Meta Info -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center space-x-2">
                                    @if($item['author'])
                                    <img src="{{ $item['author']->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($item['author']->name) }}"
                                         alt="{{ $item['author']->name }}"
                                         class="w-6 h-6 rounded-full">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $item['author']->name }}</span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-500">
                                    Updated {{ $item['last_updated']->diffForHumans() }}
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-4">
                                <div class="flex items-center justify-between mb-1 text-xs text-gray-600 dark:text-gray-400">
                                    <span>Progress</span>
                                    <span>{{ round(($item['published_parts'] / $item['total_parts']) * 100) }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden bg-gray-200 rounded-full dark:bg-gray-700">
                                    <div class="h-full transition-all duration-500 {{ $item['is_complete'] ? 'bg-green-500' : 'bg-blue-500' }}"
                                         style="width: {{ ($item['published_parts'] / $item['total_parts']) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
