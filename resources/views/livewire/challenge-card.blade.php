<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
    <div class="p-6">
        <div class="flex items-start justify-between mb-3">
            <h3 class="text-lg font-bold">{{ $challenge->title }}</h3>
            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded font-semibold">
                {{ ucfirst($challenge->difficulty) }}
            </span>
        </div>

        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($challenge->description, 100) }}</p>

        <div class="space-y-2 mb-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Goal:</span>
                <span class="font-semibold">{{ $challenge->goal }} {{ $challenge->type }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Reward:</span>
                <span class="font-semibold text-green-600">+{{ $challenge->reward_points }} points</span>
            </div>
            @if($isJoined)
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(100, ($userProgress / $challenge->goal) * 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-600">Progress: {{ $userProgress }}/{{ $challenge->goal }}</p>
            @endif
        </div>

        @if(!auth()->check())
            <button class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Login to Join
            </button>
        @elseif(!$isJoined)
            <button 
                wire:click="joinChallenge"
                @if($isProcessing) disabled @endif
                class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
            >
                @if($isProcessing) Joining... @else Join Challenge @endif
            </button>
        @else
            <button class="w-full px-4 py-2 bg-green-500 text-white rounded" disabled>
                ✓ Joined
            </button>
        @endif
    </div>
</div>
