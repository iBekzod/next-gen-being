<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-bold mb-4">🔥 Your Streaks</h3>

    @if($isLoading)
        <div class="text-center text-gray-500">Loading...</div>
    @else
        <div class="space-y-4">
            <!-- Reading Streak -->
            <div class="border-l-4 border-blue-500 pl-4 py-2">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">📚 Reading Streak</h4>
                    <span class="text-2xl font-bold text-blue-500">{{ $readingStreak->current_streak ?? 0 }}</span>
                </div>
                <p class="text-sm text-gray-600">Days</p>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min(100, ($readingStreak->current_streak ?? 0) * 5) }}%"></div>
                </div>
            </div>

            <!-- Writing Streak -->
            <div class="border-l-4 border-green-500 pl-4 py-2">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">✍️ Writing Streak</h4>
                    <span class="text-2xl font-bold text-green-500">{{ $writingStreak->current_streak ?? 0 }}</span>
                </div>
                <p class="text-sm text-gray-600">Days</p>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(100, ($writingStreak->current_streak ?? 0) * 5) }}%"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 mt-4">
                <button 
                    wire:click="recordActivity('reading')"
                    class="flex-1 px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                >
                    📚 Log Read
                </button>
                <button 
                    wire:click="recordActivity('writing')"
                    class="flex-1 px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm"
                >
                    ✍️ Log Write
                </button>
            </div>
        </div>
    @endif
</div>
