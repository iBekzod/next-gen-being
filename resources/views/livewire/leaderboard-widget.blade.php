<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-bold mb-4">🏆 Leaderboards</h3>

    <div class="space-y-4">
        <!-- Type Filter -->
        <div class="flex gap-2 flex-wrap">
            @foreach(['creators', 'readers', 'engagers', 'trending'] as $t)
                <button 
                    wire:click="$set('type', '{{ $t }}')"
                    class="px-3 py-1 rounded text-sm {{ $type === $t ? 'bg-blue-500 text-white' : 'bg-gray-200' }}"
                >
                    {{ ucfirst($t) }}
                </button>
            @endforeach
        </div>

        <!-- Time Range Filter -->
        <div class="flex gap-2 flex-wrap">
            @foreach(['7days' => '7 Days', '30days' => '30 Days', 'all' => 'All Time'] as $range => $label)
                <button 
                    wire:click="$set('timeRange', '{{ $range }}')"
                    class="px-3 py-1 rounded text-sm {{ $timeRange === $range ? 'bg-green-500 text-white' : 'bg-gray-200' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Leaderboard List -->
        @if($isLoading)
            <div class="text-center text-gray-500">Loading...</div>
        @else
            <div class="space-y-2">
                @forelse($leaderboard as $index => $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded hover:bg-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-lg w-8">{{ $index + 1 }}</span>
                            <div>
                                <p class="font-semibold">{{ $item['name'] ?? 'User' }}</p>
                                <p class="text-sm text-gray-600">{{ $item['subtitle'] ?? '' }}</p>
                            </div>
                        </div>
                        <span class="font-bold text-lg text-blue-500">{{ $item['value'] ?? 0 }}</span>
                    </div>
                @empty
                    <p class="text-center text-gray-500">No data available</p>
                @endforelse
            </div>

            @if(auth()->check() && $userRank)
                <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm text-gray-700">Your Rank: <span class="font-bold text-blue-600">#{{ $userRank }}</span></p>
                </div>
            @endif
        @endif
    </div>
</div>
