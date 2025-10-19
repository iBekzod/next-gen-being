<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <!-- Hero Section with Featured Posts -->
    @if($this->featuredPosts->count() > 0)
    <section class="mb-12">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            @foreach($this->featuredPosts as $index => $post)
            <a href="{{ route('posts.show', $post->slug) }}" class="group block {{ $index === 0 ? 'lg:col-span-2' : '' }}">
                <div class="relative overflow-hidden rounded-2xl bg-gray-900 {{ $index === 0 ? 'h-96' : 'h-64' }} cursor-pointer">
                    @if($post->featured_image)
                    <img src="{{ $post->featured_image }}"
                         alt="{{ $post->title }}"
                         class="absolute inset-0 object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <div class="flex items-center mb-3 space-x-3">
                            <span class="px-3 py-1 bg-{{ $post->category->color ?? 'blue' }}-500 text-white text-xs font-medium rounded-full">
                                {{ $post->category->name }}
                            </span>
                            <span class="text-sm text-white/80">{{ $post->read_time }} min read</span>
                        </div>
                        <h2 class="text-white font-bold {{ $index === 0 ? 'text-2xl lg:text-3xl' : 'text-xl' }} line-clamp-2 mb-2">
                            {{ $post->title }}
                        </h2>
                        <p class="mb-3 text-sm text-white/90 line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $post->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) }}"
                                     alt="{{ $post->author->name }}"
                                     class="w-8 h-8 rounded-full">
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $post->author->name }}</p>
                                    <p class="text-xs text-white/70">{{ $post->published_at->format('M j, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4 text-sm text-white/70">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ number_format($post->views_count) }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ number_format($post->likes_count) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Filters and Search -->
    <div class="flex flex-col mb-8 space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
        <div class="flex items-center space-x-4">
            <button wire:click="$toggle('showFilters')"
                    class="flex items-center px-4 py-2 text-sm font-medium transition-all duration-200 border-2 border-gray-300 rounded-lg bg-white hover:bg-blue-50 hover:border-blue-500 hover:shadow-md dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700/70">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filters
            </button>

            @if($search || $category || $selectedTag || $contentType !== 'all')
            <button wire:click="clearFilters"
                    class="px-4 py-2 text-sm font-medium text-red-600 transition-all duration-200 hover:bg-red-50 hover:text-red-700 rounded-lg hover:shadow-md dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20">
                Clear all filters
            </button>
            @endif
        </div>

        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search posts..."
                       class="w-64 py-2 pl-10 pr-4 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-800 dark:border-slate-600 dark:text-slate-100">
                <svg class="absolute w-4 h-4 text-gray-400 transform -translate-y-1/2 left-3 top-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <select wire:model.live="sortBy" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-800 dark:border-slate-600 dark:text-slate-100">
                <option value="latest">Latest</option>
                <option value="popular">Popular</option>
                <option value="trending">Trending</option>
                <option value="oldest">Oldest</option>
            </select>
        </div>
    </div>

    <!-- Advanced Filters -->
    @if($showFilters)
    <div x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="p-6 mb-8 rounded-lg bg-gray-50">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Category</label>
                <select wire:model.live="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($this->availableCategories as $cat)
                    <option value="{{ $cat->slug }}">{{ $cat->name }} ({{ $cat->published_posts_count??0 }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Tag</label>
                <select wire:model.live="selectedTag" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Tags</option>
                    @foreach($this->popularTags as $tag)
                    <option value="{{ $tag->slug }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Content Type</label>
                <select wire:model.live="contentType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All Content</option>
                    <option value="free">Free Content</option>
                    <option value="premium">Premium Content</option>
                </select>
            </div>
        </div>
    </div>
    @endif

    <!-- Posts Grid -->
    <div class="grid grid-cols-1 gap-8 mb-8 md:grid-cols-2 lg:grid-cols-3">
        @forelse($this->posts as $post)
        <article class="overflow-hidden transition-all duration-300 bg-white dark:bg-slate-900 border-2 border-gray-300 dark:border-slate-700 shadow-md group rounded-xl hover:shadow-xl hover:border-blue-500 dark:hover:border-blue-500 hover:-translate-y-1">
            <div class="relative overflow-hidden aspect-w-16 aspect-h-9">
                @if($post->featured_image)
                <img src="{{ $post->featured_image }}"
                     alt="{{ $post->title }}"
                     class="object-cover w-full h-48 transition-transform duration-300 group-hover:scale-105">
                @else
                <div class="flex items-center justify-center w-full h-48 bg-gradient-to-br from-blue-500 to-purple-600">
                    <svg class="w-12 h-12 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                @endif

                @if($post->is_premium)
                <div class="absolute top-3 right-3">
                    <span class="flex items-center px-2 py-1 text-xs font-medium text-white rounded-full bg-gradient-to-r from-yellow-400 to-orange-500">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Premium
                    </span>
                </div>
                @endif
            </div>

            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="px-3 py-1 bg-{{ $post->category->color ?? 'blue' }}-100 text-{{ $post->category->color ?? 'blue' }}-800 text-xs font-medium rounded-full">
                        {{ $post->category->name }}
                    </span>
                    <span class="text-sm text-gray-500">{{ $post->read_time }} min read</span>
                </div>

                <h3 class="mb-3 text-lg font-bold text-gray-900 transition-colors line-clamp-2 group-hover:text-blue-600">
                    <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                </h3>

                <p class="mb-4 text-sm text-gray-600 line-clamp-3">{{ $post->excerpt }}</p>

                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <img src="{{ $post->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) }}"
                             alt="{{ $post->author->name }}"
                             class="w-8 h-8 rounded-full">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $post->author->name }}</p>
                            <p class="text-xs text-gray-500">{{ $post->published_at->format('M j, Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm text-gray-500">
                    <div class="flex items-center space-x-4">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ number_format($post->views_count) }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                            </svg>
                            {{ number_format($post->likes_count) }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            {{ number_format($post->comments_count) }}
                        </span>
                    </div>
                    <div class="flex space-x-1">
                        @foreach($post->tags->take(2) as $tag)
                        <span class="px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded">
                            {{ $tag->name }}
                        </span>
                        @endforeach
                        @if($post->tags->count() > 2)
                        <span class="px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded">
                            +{{ $post->tags->count() - 2 }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </article>
        @empty
        <div class="py-12 text-center col-span-full">
            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No posts found</h3>
            <p class="mt-1 text-gray-500">Try adjusting your search criteria or filters.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    {{ $this->posts->links() }}
</div>



