<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Analytics Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Track your content performance and audience growth</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="setPeriod('week')" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedPeriod === 'week' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                Week
            </button>
            <button wire:click="setPeriod('month')" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedPeriod === 'month' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                Month
            </button>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Views -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Total Views</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($dashboardStats['total_views']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    <span class="{{ ($growthComparison['view_growth'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ ($growthComparison['view_growth'] ?? 0) >= 0 ? '+' : '' }}{{ ($growthComparison['view_growth'] ?? 0) }}%
                    </span>
                    from last {{ $selectedPeriod }}
                </p>
            </div>
        </div>

        <!-- Total Engagement -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Total Engagement</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($dashboardStats['total_likes'] + $dashboardStats['total_comments']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $dashboardStats['total_likes'] }} likes • {{ $dashboardStats['total_comments'] }} comments</p>
            </div>
        </div>

        <!-- Engagement Rate -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Engagement Rate</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $dashboardStats['engagement_rate'] }}%</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400">Industry avg: 2-5%</p>
            </div>
        </div>

        <!-- Total Posts -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Total Posts</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $dashboardStats['total_posts'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $dashboardStats['avg_post_views'] }} avg views per post</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Performance Trend</h2>
                <div class="flex gap-2">
                    <button wire:click="setMetric('views')" class="px-3 py-1 text-sm rounded font-medium transition {{ $selectedMetric === 'views' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                        Views
                    </button>
                    <button wire:click="setMetric('engagement')" class="px-3 py-1 text-sm rounded font-medium transition {{ $selectedMetric === 'engagement' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                        Engagement
                    </button>
                </div>
            </div>

            @if (!empty($chartData['labels']))
                <div class="h-80">
                    <canvas id="performanceChart"></canvas>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener('livewire:navigated', () => {
                        const ctx = document.getElementById('performanceChart');
                        if (ctx && !window.performanceChartInstance) {
                            window.performanceChartInstance = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: @json($chartData['labels'] ?? []),
                                    datasets: @json($chartData['datasets'] ?? [])
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: 'top',
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                        }
                                    }
                                }
                            });
                        }
                    });
                </script>
            @else
                <div class="h-80 flex items-center justify-center text-gray-500 dark:text-gray-400">
                    <p>No data available yet</p>
                </div>
            @endif
        </div>

        <!-- Audience Insights -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Audience</h2>
            <div class="space-y-6">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Active Readers (30d)</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($audienceInsights['active_readers_30d'] ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Avg Session Duration</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ intval(($audienceInsights['avg_session_duration'] ?? 0) / 60) }}m {{ ($audienceInsights['avg_session_duration'] ?? 0) % 60 }}s</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Reader Retention</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $audienceInsights['retention_rate'] ?? 0 }}%</p>
                </div>

                @if (!empty($audienceInsights['top_countries']))
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Top Countries</p>
                    <div class="space-y-2">
                        @foreach ($audienceInsights['top_countries'] as $country => $count)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">{{ $country }}</span>
                                <span class="text-gray-900 dark:text-white font-semibold">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Performing Posts -->
    @if (!empty($trendingPosts))
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Top Performing Posts</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Post</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-900 dark:text-white">Views</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-900 dark:text-white">Engagement</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-900 dark:text-white">Engagement Rate</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-900 dark:text-white">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trendingPosts as $post)
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="py-4 px-4">
                                <p class="font-medium text-gray-900 dark:text-white line-clamp-2">{{ $post['title'] }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ \Carbon\Carbon::parse($post['published_at'])->format('M d, Y') }}</p>
                            </td>
                            <td class="text-center py-4 px-4">
                                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($post['views']) }}</span>
                            </td>
                            <td class="text-center py-4 px-4">
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $post['likes'] + $post['comments'] }}</span>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{$post['likes']}} ❤️ {{$post['comments']}} 💬</p>
                            </td>
                            <td class="text-center py-4 px-4">
                                <span class="font-semibold text-green-600 dark:text-green-400">{{ $post['engagement_rate'] }}%</span>
                            </td>
                            <td class="text-center py-4 px-4">
                                <a href="/posts/{{ $post['slug'] }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
