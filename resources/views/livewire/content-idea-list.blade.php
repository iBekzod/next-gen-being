<div class="space-y-4">
    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4">
        <select 
            wire:model.live="filter"
            class="px-4 py-2 border rounded"
        >
            <option value="active">Active Ideas</option>
            <option value="archived">Archived Ideas</option>
            <option value="all">All Ideas</option>
        </select>
    </div>

    @if($isLoading)
        <div class="text-center text-gray-500">Loading...</div>
    @else
        <div class="space-y-2">
            @forelse($ideas as $idea)
                <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-bold">{{ $idea->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($idea->description, 100) }}</p>
                            <div class="flex gap-2 mt-2">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                    {{ $idea->content_type }}
                                </span>
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">
                                    Difficulty: {{ $idea->difficulty_score }}/10
                                </span>
                            </div>
                        </div>
                        <button 
                            wire:click="deleteIdea({{ $idea->id }})"
                            class="text-red-500 hover:text-red-600 ml-4"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">No ideas yet</p>
            @endforelse
        </div>
    @endif
</div>
