<div class="space-y-8">
    <!-- Header -->
    <div class="mb-8 border-b border-gray-200 dark:border-slate-700 pb-6">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
            @switch($type)
                @case('similar')
                    Related Articles
                    @break
                @case('personalized')
                    Recommended For You
                    @break
                @case('trending')
                    Trending Now
                    @break
                @case('followed')
                    From Authors You Follow
                    @break
                @default
                    More Articles
            @endswitch
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">
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
        <div class="space-y-4">
            @foreach ($recommendations as $rec)
                <a href="{{ route('posts.show', $rec['slug']) }}"
                   @click="$wire.trackClick({{ $rec['id'] }})"
                   class="group flex gap-5 p-4 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all duration-300">

                    <!-- Thumbnail -->
                    <div class="flex-shrink-0 w-28 h-28">
                        @if ($rec['featured_image'])
                            <img src="{{ $rec['featured_image'] }}"
                                 alt="{{ $rec['title'] }}"
                                 class="w-full h-full object-cover rounded-lg group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-8 h-8 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2m2 2a2 2 0 002-2m-2 2v-13a2 2 0 00-2-2H9a2 2 0 00-2 2v13a2 2 0 002 2h10a2 2 0 002-2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                        <!-- Top Section -->
                        <div>
                            <!-- Category & Title -->
                            <div class="mb-2">
                                <a href="{{ route('categories.show', $rec['category_slug']) }}"
                                   class="inline-block text-xs font-bold uppercase tracking-wide text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-1">
                                    {{ $rec['category_name'] }}
                                </a>
                            </div>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition mb-2">
                                {{ $rec['title'] }}
                            </h4>
                        </div>

                        <!-- Bottom Section: Meta + Views -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <a href="{{ route('bloggers.profile', $rec['author_slug']) }}"
                                   class="text-xs text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition truncate">
                                    {{ $rec['author_name'] }}
                                </a>
                                <span class="text-gray-300 dark:text-gray-600 text-xs">•</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $rec['published_at'] }}</span>
                            </div>
                            <!-- View Count - Prominent -->
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-slate-900 rounded-full flex-shrink-0 whitespace-nowrap">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ number_format($rec['views']) }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="py-12 px-6 text-center bg-gray-50 dark:bg-slate-900/50 rounded-xl border border-gray-200 dark:border-slate-700">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4"/>
            </svg>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No recommendations available yet</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">
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
        <div class="pt-6 mt-4 text-center">
            <a href="{{ route('posts.index') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-medium text-sm rounded-lg transition-all duration-300">
                <span>View All Articles</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>
    @endif
</div>
