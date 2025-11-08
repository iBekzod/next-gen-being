<div class="space-y-4">
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            @switch($type)
                @case('similar')
                    📚 Similar Articles
                    @break
                @case('personalized')
                    ✨ Recommended For You
                    @break
                @case('trending')
                    🔥 Trending Now
                    @break
                @case('followed')
                    👥 From Authors You Follow
                    @break
                @default
                    📰 More Articles
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
                   class="group block p-4 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-lg transition-all">

                    <div class="flex gap-4">
                        <!-- Thumbnail -->
                        @if ($rec['featured_image'])
                            <img src="{{ $rec['featured_image'] }}"
                                 alt="{{ $rec['title'] }}"
                                 class="w-16 h-16 object-cover rounded flex-shrink-0 group-hover:scale-105 transition-transform">
                        @else
                            <div class="w-16 h-16 rounded flex-shrink-0 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                <span class="text-xl">📝</span>
                            </div>
                        @endif

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <!-- Category Badge -->
                            <a href="{{ route('categories.show', $rec['category_slug']) }}"
                               class="inline-block text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-1">
                                {{ $rec['category_name'] }}
                            </a>

                            <!-- Title -->
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                                {{ $rec['title'] }}
                            </h4>

                            <!-- Meta -->
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('bloggers.profile', $rec['author_slug']) }}"
                                       class="hover:text-gray-900 dark:hover:text-white transition">
                                        By {{ $rec['author_name'] }}
                                    </a>
                                    <span class="text-gray-400">•</span>
                                    <span>{{ $rec['published_at'] }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span>👁️ {{ number_format($rec['views']) }}</span>
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

    <!-- View More Link -->
    @if (!empty($recommendations))
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
            <a href="{{ route('posts.index') }}"
               class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition">
                Explore all posts
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    @endif
</div>
