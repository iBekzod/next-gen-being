<div class="space-y-4">
    <!-- Month Navigation -->
    <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-between">
        <button 
            wire:click="previousMonth"
            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
        >
            ← Prev
        </button>
        <h2 class="text-xl font-bold">{{ now()->createFromDate($year, $month, 1)->format('F Y') }}</h2>
        <button 
            wire:click="nextMonth"
            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
        >
            Next →
        </button>
    </div>

    @if($isLoading)
        <div class="text-center text-gray-500">Loading...</div>
    @else
        <!-- Scheduled Posts -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold mb-4">📅 Scheduled Posts</h3>
            @forelse($scheduledPosts as $post)
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded mb-2">
                    <div>
                        <p class="font-semibold">{{ Str::limit($post->title, 40) }}</p>
                        <p class="text-sm text-gray-600">{{ $post->scheduled_at->format('M d, Y @ h:i A') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-200 text-blue-800 rounded text-sm">
                        {{ ucfirst($post->status) }}
                    </span>
                </div>
            @empty
                <p class="text-gray-500">No scheduled posts</p>
            @endforelse
        </div>

        <!-- Drafts -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold mb-4">📝 Drafts</h3>
            @forelse($drafts as $draft)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded mb-2">
                    <p class="font-semibold">{{ Str::limit($draft->title, 40) }}</p>
                    <button class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600">
                        Schedule
                    </button>
                </div>
            @empty
                <p class="text-gray-500">No drafts</p>
            @endforelse
        </div>
    @endif
</div>
