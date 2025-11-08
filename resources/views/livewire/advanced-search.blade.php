<div class="space-y-6">
    <!-- Search Header -->
    <div class="sticky top-0 z-40 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-gray-700 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Search Bar -->
            <div class="relative">
                <input type="text"
                       wire:model.live.debounce.300ms="query"
                       placeholder="Search articles, tutorials, news..."
                       class="w-full px-4 py-3 pl-12 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-800 dark:text-white">
                <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Quick Actions -->
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button wire:click="toggleFilters"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition
                            {{ $showFilters ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filters {{ $activeFilters > 0 ? '(' . $activeFilters . ')' : '' }}
                    </button>

                    @if($activeFilters > 0)
                    <button wire:click="resetFilters"
                            class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                        Clear all
                    </button>
                    @endif
                </div>

                <!-- Sort Dropdown -->
                <select wire:model.live="sortBy"
                        class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-800 dark:text-white">
                    @foreach($availableFilters['sort_options'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    @if($showFilters)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-50 dark:bg-slate-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Categories -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">Categories</label>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($availableFilters['categories'] as $category)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 p-2 rounded transition">
                                <input type="checkbox"
                                       wire:model.live="selectedCategories"
                                       value="{{ $category->id }}"
                                       class="rounded border-gray-300 dark:border-gray-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Tags -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">Tags</label>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($availableFilters['tags']->take(15) as $tag)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 p-2 rounded transition">
                                <input type="checkbox"
                                       wire:model.live="selectedTags"
                                       value="{{ $tag->id }}"
                                       class="rounded border-gray-300 dark:border-gray-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $tag->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">({{ $tag->posts_count }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Content Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">Content Type</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 p-2 rounded transition">
                            <input type="radio"
                                   wire:model.live="contentType"
                                   value=""
                                   class="border-gray-300 dark:border-gray-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">All Types</span>
                        </label>
                        @foreach($availableFilters['content_types'] as $value => $label)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 p-2 rounded transition">
                                <input type="radio"
                                       wire:model.live="contentType"
                                       value="{{ $value }}"
                                       class="border-gray-300 dark:border-gray-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Engagement Level -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">Engagement</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 p-2 rounded transition">
                            <input type="radio"
                                   wire:model.live="engagement"
                                   value=""
                                   class="border-gray-300 dark:border-gray-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Any</span>
                        </label>
                        @foreach($availableFilters['engagement_levels'] as $value => $label)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 p-2 rounded transition">
                                <input type="radio"
                                       wire:model.live="engagement"
                                       value="{{ $value }}"
                                       class="border-gray-300 dark:border-gray-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Read Time -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">Read Time</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 p-2 rounded transition">
                            <input type="radio"
                                   wire:model.live="readTime"
                                   value=""
                                   class="border-gray-300 dark:border-gray-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Any</span>
                        </label>
                        @foreach($availableFilters['read_times'] as $value => $label)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 p-2 rounded transition">
                                <input type="radio"
                                       wire:model.live="readTime"
                                       value="{{ $value }}"
                                       class="border-gray-300 dark:border-gray-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">Date Range</label>
                    <div class="space-y-2">
                        <input type="date"
                               wire:model.live="dateFrom"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                               placeholder="From">
                        <input type="date"
                               wire:model.live="dateTo"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                               placeholder="To">
                    </div>
                </div>

                <!-- View Count -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">View Count</label>
                    <div class="space-y-2">
                        <input type="number"
                               wire:model.live="minViews"
                               placeholder="Min"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white">
                        <input type="number"
                               wire:model.live="maxViews"
                               placeholder="Max"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white">
                    </div>
                </div>

                <!-- Premium Content -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">Content Type</label>
                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 p-2 rounded transition">
                        <input type="checkbox"
                               wire:model.live="isPremium"
                               class="rounded border-gray-300 dark:border-gray-600">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Premium Only</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Results Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Results Header -->
        @if($query || $activeFilters > 0)
        <div class="mb-6">
            @if($query)
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Results for "{{ $query }}"
            </h2>
            @if(!empty($searchStats))
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                Found <strong>{{ $searchStats['total_results'] }}</strong> articles
                with <strong>{{ number_format($searchStats['total_views']) }}</strong> total views
                and <strong>{{ number_format($searchStats['total_engagement']) }}</strong> total engagement
            </p>
            @endif
            @endif
        </div>
        @endif

        <!-- Results Grid -->
        @if($results->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach($results as $post)
                <article class="group bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:border-blue-500 dark:hover:border-blue-500 transition-all">
                    <!-- Featured Image -->
                    @if($post->featured_image)
                        <img src="{{ $post->featured_image }}"
                             alt="{{ $post->title }}"
                             class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                            <span class="text-4xl">📰</span>
                        </div>
                    @endif

                    <!-- Content -->
                    <div class="p-4">
                        <!-- Category & Meta -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-block text-xs font-semibold tracking-wide uppercase text-blue-600 dark:text-blue-400">
                                {{ $post->category->name }}
                            </span>
                            @if($post->is_premium)
                            <span class="text-xs font-semibold text-yellow-600 dark:text-yellow-400">💎 Premium</span>
                            @endif
                        </div>

                        <!-- Title -->
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                            <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>

                        <!-- Excerpt -->
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-4">
                            {{ $post->excerpt }}
                        </p>

                        <!-- Tags -->
                        @if($post->tags->count() > 0)
                        <div class="flex flex-wrap gap-1 mb-4">
                            @foreach($post->tags->take(3) as $tag)
                                <a href="{{ route('tags.show', $tag->slug) }}"
                                   class="inline-block text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-blue-100 dark:hover:bg-blue-900 transition">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                        @endif

                        <!-- Meta Info -->
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span>👁️ {{ number_format($post->views_count) }}</span>
                                <span>❤️ {{ number_format($post->likes_count) }}</span>
                                <span>💬 {{ $post->comments_count }}</span>
                            </div>
                            <span>{{ $post->read_time }} min</span>
                        </div>

                        <!-- Author -->
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <img src="{{ $post->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) }}"
                                 alt="{{ $post->author->name }}"
                                 class="w-6 h-6 rounded-full">
                            <div class="text-xs">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $post->author->name }}</p>
                                <p class="text-gray-500 dark:text-gray-400">{{ $post->published_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mb-12">
            {{ $results->links() }}
        </div>
        @else
        <!-- No Results -->
        <div class="text-center py-20">
            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No results found</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                @if($query)
                    Try different keywords or adjust your filters
                @else
                    Start typing to search for articles
                @endif
            </p>
            @if($activeFilters > 0)
            <button wire:click="resetFilters" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                Clear filters and try again
            </button>
            @endif
        </div>
        @endif
    </div>
</div>
