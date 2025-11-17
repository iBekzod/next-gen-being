<div class="space-y-8">
    <!-- Header -->
    <div class="mb-8">
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
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
        <p class="text-base text-gray-600 dark:text-gray-400">
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
                   class="group flex gap-6 p-5 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-md dark:hover:shadow-lg transition-all duration-300 overflow-hidden">

                    <!-- Thumbnail -->
                    <div class="flex-shrink-0 w-32 h-32 rounded-lg overflow-hidden">
                        @if ($rec['featured_image'])
                            <img src="{{ $rec['featured_image'] }}"
                                 alt="{{ $rec['title'] }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                <svg class="w-10 h-10 text-white opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2m2 2a2 2 0 002-2m-2 2v-13a2 2 0 00-2-2H9a2 2 0 00-2 2v13a2 2 0 002 2h10a2 2 0 002-2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 flex flex-col">
                        <!-- Top Section -->
                        <div class="flex-1 mb-3">
                            <!-- Category Badge -->
                            <div class="mb-2">
                                <a href="{{ route('categories.show', $rec['category_slug']) }}"
                                   class="inline-block text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition">
                                    {{ $rec['category_name'] }}
                                </a>
                            </div>
                            <!-- Title -->
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition mb-1">
                                {{ $rec['title'] }}
                            </h4>
                        </div>

                        <!-- Footer: Meta + View Count -->
                        <div class="flex items-center justify-between gap-4 pt-3 border-t border-gray-100 dark:border-slate-700">
                            <div class="flex items-center gap-3 min-w-0 text-xs text-gray-600 dark:text-gray-400">
                                <a href="{{ route('bloggers.profile', $rec['author_slug']) }}"
                                   class="hover:text-blue-600 dark:hover:text-blue-400 transition truncate font-medium">
                                    {{ $rec['author_name'] }}
                                </a>
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <span class="text-gray-500 dark:text-gray-500 whitespace-nowrap">{{ $rec['published_at'] }}</span>
                            </div>

                            <!-- View Count - Prominent Badge -->
                            <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-full flex-shrink-0 whitespace-nowrap border border-blue-200 dark:border-blue-800">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span class="text-sm font-bold text-blue-700 dark:text-blue-300">{{ number_format($rec['views']) }}</span>
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
