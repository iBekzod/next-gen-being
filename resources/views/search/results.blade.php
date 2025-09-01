
<div class="space-y-8">
    <!-- Search Tabs -->
    <div class="flex space-x-1 border-b border-gray-200 dark:border-gray-700">
        <button wire:click="$set('searchType', 'posts')"
                class="px-4 py-2 text-sm font-medium transition-colors border-b-2 {{ $searchType === 'posts' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Articles ({{ $postsCount }})
        </button>
        <button wire:click="$set('searchType', 'authors')"
                class="px-4 py-2 text-sm font-medium transition-colors border-b-2 {{ $searchType === 'authors' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Authors ({{ $authorsCount }})
        </button>
    </div>

    <!-- Search Results -->
    <div>
        @if($searchType === 'posts')
            @forelse($posts as $post)
            <article class="pb-6 mb-6 border-b border-gray-200 dark:border-gray-700 last:border-0">
                <h2 class="mb-2 text-xl font-bold text-gray-900 transition-colors dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                    <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                </h2>
                <p class="mb-3 text-gray-600 dark:text-gray-400">{{ $post->excerpt }}</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                        <span>By {{ $post->author->name }}</span>
                        <span>{{ $post->published_at->format('M j, Y') }}</span>
                        <span>{{ $post->read_time }} min read</span>
                    </div>
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-{{ $post->category->color ?? 'blue' }}-100 text-{{ $post->category->color ?? 'blue' }}-800 dark:bg-{{ $post->category->color ?? 'blue' }}-900 dark:text-{{ $post->category->color ?? 'blue' }}-200">
                        {{ $post->category->name }}
                    </span>
                </div>
            </article>
            @empty
            <div class="py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No articles found</h3>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Try adjusting your search terms</p>
            </div>
            @endforelse

            @if($posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                {{ $posts->links() }}
            @endif
        @endif

        @if($searchType === 'authors')
            @forelse($authors as $author)
            <div class="flex items-start space-x-4 pb-6 mb-6 border-b border-gray-200 dark:border-gray-700 last:border-0">
                <img src="{{ $author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($author->name) }}"
                    alt="{{ $author->name }}"
                    class="w-16 h-16 rounded-full">
                <div class="flex-1">
                    <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-white">
                        {{ $author->name }}
                    </h3>
                    @if($author->bio)
                    <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($author->bio, 150) }}</p>
                    @endif
                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ $author->posts_count }} articles</span>
                        <span>Joined {{ $author->created_at->format('M Y') }}</span>
                        @if($author->website)
                        <a href="{{ $author->website }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            Website
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No authors found</h3>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Try adjusting your search terms</p>
            </div>
            @endforelse

            @if($authors instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                {{ $authors->links() }}
            @endif
        @endif
    </div>
</div>
