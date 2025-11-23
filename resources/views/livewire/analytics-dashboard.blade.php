<div class="space-y-6">
    <!-- Time Range Selector -->
    <div class="bg-white rounded-lg shadow-md p-4">
        <select 
            wire:model.live="timeRange"
            class="px-4 py-2 border rounded"
        >
            <option value="7days">Last 7 Days</option>
            <option value="30days">Last 30 Days</option>
            <option value="90days">Last 90 Days</option>
            <option value="all">All Time</option>
        </select>
    </div>

    @if($isLoading)
        <div class="text-center text-gray-500 py-8">Loading analytics...</div>
    @else
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <p class="text-sm text-gray-600">Views</p>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['views'] ?? 0 }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <p class="text-sm text-gray-600">Likes</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['likes'] ?? 0 }}</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <p class="text-sm text-gray-600">Comments</p>
                <p class="text-3xl font-bold text-purple-600">{{ $stats['comments'] ?? 0 }}</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <p class="text-sm text-gray-600">Revenue</p>
                <p class="text-3xl font-bold text-yellow-600">${{ number_format($stats['revenue'] ?? 0, 2) }}</p>
            </div>
        </div>

        <!-- Top Posts -->
        @if($topPosts)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold mb-4">Top Posts</h3>
                <div class="space-y-2">
                    @foreach($topPosts as $post)
                        <div class="flex justify-between p-3 bg-gray-50 rounded">
                            <span>{{ Str::limit($post->title, 50) }}</span>
                            <span class="font-semibold">{{ $post->views }} views</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Export Button -->
        <button 
            wire:click="exportAnalytics"
            class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
            📥 Export Report
        </button>
    @endif
</div>
