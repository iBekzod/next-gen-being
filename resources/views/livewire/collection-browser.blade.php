<div class="space-y-4">
    <!-- Sort Selector -->
    <div class="bg-white rounded-lg shadow-md p-4">
        <select 
            wire:model.live="sort"
            class="px-4 py-2 border rounded"
        >
            <option value="latest">Latest</option>
            <option value="popular">Most Popular</option>
            <option value="trending">Trending</option>
        </select>
    </div>

    @if($isLoading)
        <div class="text-center text-gray-500 py-8">Loading collections...</div>
    @else
        <!-- Collections Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($collections as $collection)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-2">{{ $collection->title }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($collection->description, 100) }}</p>
                        
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                            <span>By {{ $collection->user->name }}</span>
                            <span>{{ $collection->posts_count ?? 0 }} posts</span>
                        </div>

                        <button 
                            wire:click="saveCollection({{ $collection->id }})"
                            class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                        >
                            💾 Save Collection
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8 col-span-full">No collections found</p>
            @endforelse
        </div>
    @endif
</div>
