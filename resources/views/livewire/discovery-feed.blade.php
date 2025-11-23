<div class="space-y-4">
    <!-- Feed Type Selector -->
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex gap-2 overflow-x-auto pb-2">
            @foreach(['discovery', 'home', 'trending', 'personalized'] as $type)
                <button 
                    wire:click="$set('feedType', '{{ $type }}')"
                    class="px-4 py-2 rounded text-sm whitespace-nowrap {{ $feedType === $type ? 'bg-blue-500 text-white' : 'bg-gray-200' }}"
                >
                    {{ ucfirst($type) }}
                </button>
            @endforeach
        </div>
    </div>

    @if($isLoading)
        <div class="text-center text-gray-500 py-8">Loading posts...</div>
    @else
        <div class="space-y-4">
            @forelse($posts as $post)
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-bold mb-2">{{ $post->title ?? 'Untitled' }}</h3>
                    <p class="text-gray-600 mb-4">{{ Str::limit($post->excerpt ?? '', 150) }}</p>
                    
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>{{ $post->user->name ?? 'Anonymous' }}</span>
                        <span>{{ $post->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <button wire:click="likePost({{ $post->id }})" class="text-red-500 hover:text-red-600">
                            ❤️ {{ $post->likes_count ?? 0 }}
                        </button>
                        <button class="text-blue-500 hover:text-blue-600">
                            💬 {{ $post->comments_count ?? 0 }}
                        </button>
                        <button wire:click="bookmarkPost({{ $post->id }})" class="text-yellow-500 hover:text-yellow-600">
                            🔖
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">No posts found</p>
            @endforelse
        </div>
    @endif
</div>
