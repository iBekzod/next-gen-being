<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    @switch($type)
                        @case('similar')
                            Similar Articles
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
            <div class="h-1 w-24 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"></div>
        </div>
    </div>

    <!-- Recommendations Grid -->
    @if (!empty($recommendations))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($recommendations as $rec)
                <a href="{{ route('posts.show', $rec['slug']) }}"
                   @click="$wire.trackClick({{ $rec['id'] }})"
                   class="group flex flex-col h-full bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-lg transition-all duration-300 overflow-hidden">

                    <!-- Image Container -->
                    <div class="relative w-full h-48 overflow-hidden bg-gray-100 dark:bg-slate-900">
                        @if ($rec['featured_image'])
                            <img src="{{ $rec['featured_image'] }}"
                                 alt="{{ $rec['title'] }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2m2 2a2 2 0 002-2m-2 2v-13a2 2 0 00-2-2H9a2 2 0 00-2 2v13a2 2 0 002 2h10a2 2 0 002-2z"/>
                                </svg>
                            </div>
                        @endif
                        <!-- Category Badge Overlay -->
                        <div class="absolute top-3 left-3">
                            <a href="{{ route('categories.show', $rec['category_slug']) }}"
                               class="inline-block text-xs font-bold uppercase tracking-wide text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-full transition shadow-md">
                                {{ $rec['category_name'] }}
                            </a>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex flex-col flex-1 p-5">
                        <!-- Title -->
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition mb-2">
                            {{ $rec['title'] }}
                        </h4>

                        <!-- Spacer -->
                        <div class="flex-1"></div>

                        <!-- Footer Meta -->
                        <div class="pt-4 border-t border-gray-100 dark:border-slate-700 space-y-3">
                            <!-- Author & Date -->
                            <div class="flex items-center gap-3">
                                <a href="{{ route('bloggers.profile', $rec['author_slug']) }}"
                                   class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition truncate">
                                    {{ $rec['author_name'] }}
                                </a>
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $rec['published_at'] }}</span>
                            </div>

                            <!-- Views -->
                            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span>{{ number_format($rec['views']) }} views</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="py-12 px-6 text-center bg-gray-50 dark:bg-slate-900/50 rounded-xl border border-gray-200 dark:border-slate-700">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17.25m20-11.002c5.5 0 10 4.747 10 11.002M12 6.253N12 3m0 13.002c-5.5 0-10-4.747-10-11"/>
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
        <div class="pt-8 mt-4 border-t border-gray-200 dark:border-slate-700 text-center">
            <a href="{{ route('posts.index') }}"
               class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                <span>Explore All Posts</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>
    @endif
</div>
