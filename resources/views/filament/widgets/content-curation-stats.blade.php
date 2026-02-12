<div class="space-y-6">
    @php
        $stats = $this->getStats();
        $aggStats = $this->getAggregationStats();
        $pipelineStatus = $this->getPipelineStatus();
    @endphp

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Content Sources -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Content Sources</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_sources'] }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                        {{ $stats['active_sources'] }} active
                    </p>
                </div>
                <div class="text-blue-500 text-4xl">
                    <i class="heroicon-o-globe"></i>
                </div>
            </div>
        </div>

        <!-- Articles Collected -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Articles Collected</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['articles_total'] }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                        {{ $stats['articles_today'] }} today
                    </p>
                </div>
                <div class="text-purple-500 text-4xl">
                    <i class="heroicon-o-archive-box"></i>
                </div>
            </div>
        </div>

        <!-- Aggregations -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Content Groups</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['aggregations_total'] }}</p>
                    <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                        {{ $stats['aggregations_pending'] }} pending
                    </p>
                </div>
                <div class="text-orange-500 text-4xl">
                    <i class="heroicon-o-squares-2x2"></i>
                </div>
            </div>
        </div>

        <!-- Curated Posts -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Curated Posts</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['curated_posts'] }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                        {{ $stats['published_posts'] }} published
                    </p>
                </div>
                <div class="text-green-500 text-4xl">
                    <i class="heroicon-o-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Paraphrase Quality</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Average Confidence</span>
                    <span class="text-lg font-bold text-blue-600">{{ $stats['avg_confidence_score'] }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Fact Verified</span>
                    <span class="text-lg font-bold text-green-600">{{ $stats['fact_verified_count'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Aggregation Confidence</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">High (85%+)</span>
                    <span class="text-lg font-bold text-green-600">{{ $aggStats['high_confidence'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Medium (75-85%)</span>
                    <span class="text-lg font-bold text-yellow-600">{{ $aggStats['medium_confidence'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Low (&lt;75%)</span>
                    <span class="text-lg font-bold text-red-600">{{ $aggStats['low_confidence'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Multi-Language</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Translations Created</span>
                    <span class="text-lg font-bold text-purple-600">{{ $stats['translations_count'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Tutorials</span>
                    <span class="text-lg font-bold text-blue-600">{{ $stats['tutorials_published'] }}/{{ $stats['tutorials_total'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pipeline Status -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Pipeline Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    @if ($pipelineStatus['sources_active'])
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-md bg-green-100">
                            <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    @else
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-md bg-gray-100">
                            <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        Scraping
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $pipelineStatus['last_scrape_ago'] }}
                    </p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    @if ($stats['aggregations_pending'] == 0)
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-md bg-green-100">
                            <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    @else
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-md bg-yellow-100">
                            <svg class="h-4 w-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        Deduplication
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $stats['aggregations_pending'] }} pending
                    </p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    @if ($pipelineStatus['has_pending_aggregations'])
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-md bg-yellow-100">
                            <svg class="h-4 w-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    @else
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-md bg-green-100">
                            <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        Paraphrasing
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $stats['draft_posts'] }} drafts
                    </p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-md bg-blue-100">
                        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        Translation
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $stats['translations_count'] }} versions
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
