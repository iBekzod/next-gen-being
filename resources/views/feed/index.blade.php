<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                My Feed
            </h2>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Following {{ $followingCount }} {{ $followingCount === 1 ? 'blogger' : 'bloggers' }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('feed.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        {{-- Content Type Filter --}}
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Content Type
                            </label>
                            <select name="type"
                                    id="type"
                                    onchange="this.form.submit()"
                                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">All Posts</option>
                                <option value="free" {{ $currentType === 'free' ? 'selected' : '' }}>Free</option>
                                <option value="premium" {{ $currentType === 'premium' ? 'selected' : '' }}>Premium</option>
                                <option value="tutorial" {{ $currentType === 'tutorial' ? 'selected' : '' }}>Tutorials</option>
                            </select>
                        </div>

                        {{-- Category Filter --}}
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Category
                            </label>
                            <select name="category"
                                    id="category"
                                    onchange="this.form.submit()"
                                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" {{ $currentCategory === $category->slug ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sort Filter --}}
                        <div>
                            <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sort By
                            </label>
                            <select name="sort"
                                    id="sort"
                                    onchange="this.form.submit()"
                                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="latest" {{ $currentSort === 'latest' ? 'selected' : '' }}>Latest</option>
                                <option value="popular" {{ $currentSort === 'popular' ? 'selected' : '' }}>Popular</option>
                                <option value="trending" {{ $currentSort === 'trending' ? 'selected' : '' }}>Trending</option>
                            </select>
                        </div>

                        {{-- Reset Button --}}
                        <div class="flex items-end">
                            <a href="{{ route('feed.index') }}"
                               class="w-full text-center inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Reset Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Posts Feed --}}
            @if($posts->count() > 0)
                <div class="space-y-6">
                    @foreach($posts as $post)
                        <article class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300">
                            <div class="p-6">
                                <div class="flex items-start gap-4">
                                    {{-- Featured Image --}}
                                    @if($post->featured_image)
                                        <div class="flex-shrink-0 hidden md:block">
                                            <img src="{{ asset('storage/' . $post->featured_image) }}"
                                                 alt="{{ $post->title }}"
                                                 class="w-48 h-32 object-cover rounded-lg">
                                        </div>
                                    @endif

                                    {{-- Post Content --}}
                                    <div class="flex-grow">
                                        {{-- Author Info --}}
                                        <div class="flex items-center gap-3 mb-3">
                                            @if($post->author->avatar)
                                                <img src="{{ asset('storage/' . $post->author->avatar) }}"
                                                     alt="{{ $post->author->name }}"
                                                     class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
                                                    {{ strtoupper(substr($post->author->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('bloggers.profile', $post->author->username) }}"
                                                   class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                                    {{ $post->author->name }}
                                                </a>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $post->published_at?->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Badges --}}
                                        <div class="flex gap-2 mb-2">
                                            @if($post->is_premium)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                    Premium
                                                </span>
                                            @endif
                                            @if($post->tutorial_series_id)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Tutorial Series
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $post->category->name }}
                                            </span>
                                        </div>

                                        {{-- Title & Excerpt --}}
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                            <a href="{{ route('posts.show', $post->slug) }}"
                                               class="hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $post->title }}
                                            </a>
                                        </h2>

                                        <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-2">
                                            {{ $post->excerpt }}
                                        </p>

                                        {{-- Tags --}}
                                        @if($post->tags->count() > 0)
                                            <div class="flex flex-wrap gap-2 mb-4">
                                                @foreach($post->tags->take(5) as $tag)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                        #{{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Stats --}}
                                        <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                {{ number_format($post->views_count) }} views
                                            </span>
                                            <span>{{ $post->read_time }} min read</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No posts found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            The bloggers you follow haven't published anything matching your filters yet.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('bloggers.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Discover More Bloggers
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
