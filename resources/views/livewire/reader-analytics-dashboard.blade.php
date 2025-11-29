<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Reader Analytics</h2>
            <p class="text-gray-600 mt-1">Understand your audience demographics and behavior</p>
        </div>
        <select
            wire:model="timeRange"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
            <option value="7days">Last 7 Days</option>
            <option value="30days">Last 30 Days</option>
            <option value="90days">Last 90 Days</option>
            <option value="all">All Time</option>
        </select>
    </div>

    <!-- Loading State -->
    @if($isLoading)
    <div class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>
    @else
        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Total Readers -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-blue-900">Total Readers</h3>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $analyticsData['total_readers'] ?? 0 }}</p>
                <p class="text-xs text-blue-700 mt-2">Unique visitors</p>
            </div>

            <!-- Returning Readers -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-green-900">Returning Readers</h3>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $analyticsData['returning_readers'] ?? 0 }}</p>
                <p class="text-xs text-green-700 mt-2">{{ $analyticsData['return_rate'] ?? '0' }}% return rate</p>
            </div>

            <!-- Avg Session Duration -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-purple-900">Avg. Session</h3>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ $analyticsData['avg_session_duration'] ?? '0m' }}</p>
                <p class="text-xs text-purple-700 mt-2">Time on site</p>
            </div>

            <!-- Bounce Rate -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-orange-900">Bounce Rate</h3>
                <p class="text-3xl font-bold text-orange-600 mt-2">{{ $analyticsData['bounce_rate'] ?? '0' }}%</p>
                <p class="text-xs text-orange-700 mt-2">Left without engaging</p>
            </div>
        </div>

        <!-- Reader Demographics -->
        @if(isset($analyticsData['demographics']))
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Demographics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Top Countries -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Top Countries</h4>
                    <div class="space-y-2">
                        @foreach($analyticsData['demographics']['top_countries'] ?? [] as $country)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">{{ $country['country'] ?? 'Unknown' }}</span>
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($country['percentage'] ?? 0) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600 min-w-12">{{ $country['percentage'] ?? 0 }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Device Types -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Device Types</h4>
                    <div class="space-y-2">
                        @foreach($analyticsData['demographics']['devices'] ?? [] as $device)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">{{ $device['type'] ?? 'Unknown' }}</span>
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($device['percentage'] ?? 0) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600 min-w-12">{{ $device['percentage'] ?? 0 }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Reader Activity Stream & Behavior Insights -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @livewire('reader-activity-stream')
            @livewire('reader-behavior-insights')
        </div>
    @endif
</div>
