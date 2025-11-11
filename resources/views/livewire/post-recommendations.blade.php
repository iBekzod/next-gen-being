<div class="space-y-4">
    <!-- Header -->
    <div class="mb-8 pb-4 border-b-2 border-blue-200 dark:border-blue-900/50">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3 mb-2">
            @switch($type)
                @case('similar')
                    <span class="text-3xl">📚</span>Similar Articles
                    @break
                @case('personalized')
                    <span class="text-3xl">✨</span>Recommended For You
                    @break
                @case('trending')
                    <span class="text-3xl">🔥</span>Trending Now
                    @break
                @case('followed')
                    <span class="text-3xl">👥</span>From Authors You Follow
                    @break
                @default
                    <span class="text-3xl">📰</span>More Articles
            @endswitch
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            @switch($type)
                @case('similar')
                    Explore related content in the same category and topics
                    @break
                @case('personalized')
                    Based on your reading history and interests
                    @break
                @case('trending')
                    The most viewed posts this week
                    @break
                @case('followed')
                    Latest from creators you follow
                    @break
            @endswitch
        </p>
    </div>

    <!-- Recommendations Grid -->
    @if (!empty($recommendations))
        <div class="grid gap-4">
            @foreach ($recommendations as $rec)
                <a href="{{ route('posts.show', $rec['slug']) }}"
                   @click="$wire.trackClick({{ $rec['id'] }})"
                   class="group block p-5 bg-white dark:bg-slate-800 rounded-xl border-2 border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                    <div class="flex gap-5">
                        <!-- Thumbnail -->
                        <div class="relative flex-shrink-0">
                            @if ($rec['featured_image'])
                                <img src="{{ $rec['featured_image'] }}"
                                     alt="{{ $rec['title'] }}"
                                     class="w-20 h-20 object-cover rounded-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-300 shadow-md">
                            @else
                                <div class="w-20 h-20 rounded-lg flex-shrink-0 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-md">
                                    <span class="text-2xl">📝</span>
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <!-- Category Badge -->
                            <a href="{{ route('categories.show', $rec['category_slug']) }}"
                               class="inline-block text-xs font-bold uppercase tracking-wide text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-2 bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 rounded-full transition">
                                {{ $rec['category_name'] }}
                            </a>

                            <!-- Title -->
                            <h4 class="text-base font-bold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition mb-3">
                                {{ $rec['title'] }}
                            </h4>

                            <!-- Meta -->
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <a href="{{ route('bloggers.profile', $rec['author_slug']) }}"
                                       class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-600 transition font-medium">
                                        👤 {{ $rec['author_name'] }}
                                    </a>
                                    <span class="text-gray-400">•</span>
                                    <span class="text-gray-600 dark:text-gray-400 font-medium">{{ $rec['published_at'] }}</span>
                                </div>
                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 font-semibold whitespace-nowrap">
                                    <span>👁️</span>
                                    <span>{{ number_format($rec['views']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="p-8 text-center bg-gray-50 dark:bg-slate-900/50 rounded-lg">
            <p class="text-gray-600 dark:text-gray-400">No recommendations available yet</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                @switch($type)
                    @case('personalized')
                        Explore more posts to get personalized recommendations
                        @break
                    @case('followed')
                        Follow authors to see their latest posts here
                        @break
                    @default
                        Check back soon for new content
                @endswitch
            </p>
        </div>
    @endif

    <!-- View More Button -->
    @if (!empty($recommendations))
        <div class="pt-6 mt-2 border-t-2 border-gray-200 dark:border-slate-700 text-center">
            <a href="{{ route('posts.index') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 hover:from-blue-700 hover:to-blue-800 dark:hover:from-blue-800 dark:hover:to-blue-900 text-white font-bold text-sm rounded-lg shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                <span>Explore All Posts</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>
    @endif
</div>
