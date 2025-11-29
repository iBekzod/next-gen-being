<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>

    @if($isLoading)
    <div class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    @else
        @if(count($activities) > 0)
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($activities as $activity)
            <div class="flex items-start gap-4 pb-3 border-b border-gray-200 last:border-b-0">
                <!-- Activity Icon -->
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full {{
                        match($activity['type'] ?? 'view') {
                            'view' => 'bg-blue-100 text-blue-600',
                            'comment' => 'bg-green-100 text-green-600',
                            'like' => 'bg-red-100 text-red-600',
                            'share' => 'bg-purple-100 text-purple-600',
                            default => 'bg-gray-100 text-gray-600'
                        }
                    }}">
                        @switch($activity['type'] ?? 'view')
                            @case('view')
                                👁️
                                @break
                            @case('comment')
                                💬
                                @break
                            @case('like')
                                ❤️
                                @break
                            @case('share')
                                📤
                                @break
                            @default
                                ✓
                        @endswitch
                    </div>
                </div>

                <!-- Activity Details -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">
                        {{ ucfirst($activity['type'] ?? 'Activity') }}
                    </p>
                    @if(isset($activity['user_name']))
                    <p class="text-xs text-gray-600">
                        {{ $activity['user_name'] }}
                    </p>
                    @endif
                    @if(isset($activity['description']))
                    <p class="text-xs text-gray-600 mt-1">
                        {{ $activity['description'] }}
                    </p>
                    @endif
                </div>

                <!-- Timestamp -->
                <div class="flex-shrink-0 text-right">
                    <p class="text-xs text-gray-500">
                        {{ $activity['time_ago'] ?? 'Recently' }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <p>No recent activity</p>
        </div>
        @endif
    @endif
</div>
