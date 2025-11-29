<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Behavior Insights</h3>

    @if($isLoading)
    <div class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    @else
        @if(count($insights) > 0)
        <div class="space-y-3">
            @foreach($insights as $insight)
            <div class="p-4 rounded-lg border {{
                match($insight['type']) {
                    'positive' => 'bg-green-50 border-green-200',
                    'warning' => 'bg-yellow-50 border-yellow-200',
                    'info' => 'bg-blue-50 border-blue-200',
                    default => 'bg-gray-50 border-gray-200'
                }
            }}">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 text-lg">
                        @switch($insight['type'])
                            @case('positive')
                                ✨
                                @break
                            @case('warning')
                                ⚠️
                                @break
                            @case('info')
                                ℹ️
                                @break
                            @default
                                💡
                        @endswitch
                    </div>
                    <div>
                        <p class="text-sm font-semibold {{
                            match($insight['type']) {
                                'positive' => 'text-green-900',
                                'warning' => 'text-yellow-900',
                                'info' => 'text-blue-900',
                                default => 'text-gray-900'
                            }
                        }}">
                            {{ $insight['title'] }}
                        </p>
                        <p class="text-xs mt-1 {{
                            match($insight['type']) {
                                'positive' => 'text-green-800',
                                'warning' => 'text-yellow-800',
                                'info' => 'text-blue-800',
                                default => 'text-gray-800'
                            }
                        }}">
                            {{ $insight['message'] }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <p>Loading insights...</p>
        </div>
        @endif
    @endif
</div>
