<div>
    <!-- Stats Overview -->
    @if($activeTab === 'overview')
    <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-3">
        <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                    Total Posts
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ $stats['total_posts'] }}
                </dd>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ $stats['published_posts'] }} published
                </p>
            </div>
        </div>

        <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                    Total Views
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ number_format($stats['total_views']) }}
                </dd>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Across all posts
                </p>
            </div>
        </div>

        <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                    Total Engagement
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ number_format($stats['total_likes'] + $stats['total_comments']) }}
                </dd>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ number_format($stats['total_likes']) }} likes, {{ number_format($stats['total_comments']) }} comments
                </p>
            </div>
        </div>

        <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                    Bookmarks
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ $stats['bookmarks'] }}
                </dd>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Saved articles
                </p>
            </div>
        </div>

        @if(auth()->user()->isPremium())
        <div class="overflow-hidden bg-gradient-to-r from-purple-500 to-blue-500 rounded-lg shadow">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-white/80 truncate">
                    Subscription Status
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-white">
                    Premium
                </dd>
                <p class="mt-2 text-sm text-white/80">
                    Active subscriber
                </p>
            </div>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if(auth()->user()->hasAnyRole(['admin', 'content_manager', 'blogger']))
            <a href="{{ route('posts.create') }}" class="flex items-center p-4 bg-white rounded-lg shadow dark:bg-gray-800 hover:shadow-md transition-shadow">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Write New Post</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Create content</p>
                </div>
            </a>
            @endif

            <button wire:click="setTab('posts')" class="flex items-center p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 hover:shadow-md transition-shadow">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">View Posts</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['total_posts'] }} total</p>
                </div>
            </button>

            <button wire:click="setTab('bookmarks')" class="flex items-center p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 hover:shadow-md transition-shadow">
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Bookmarks</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['bookmarks'] }} saved</p>
                </div>
            </button>

            <a href="{{ route('dashboard.settings') }}" class="flex items-center p-4 bg-white rounded-lg shadow dark:bg-gray-800 hover:shadow-md transition-shadow">
                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Settings</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Manage account</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div>
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h2>
        <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse(auth()->user()->posts()->latest()->limit(5)->get() as $post)
                <li class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $post->title }}
                            </h3>
                            <div class="flex items-center mt-1 space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $post->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($post->status === 'draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                                <span>{{ $post->created_at->diffForHumans() }}</span>
                                <span>{{ number_format($post->views_count) }} views</span>
                            </div>
                        </div>
                        <a href="{{ route('posts.edit', $post->slug) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Edit
                        </a>
                    </div>
                </li>
                @empty
                <li class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    No posts yet. Start writing!
                </li>
                @endforelse
            </ul>
        </div>
    </div>
    @endif

    <!-- Posts Tab -->
    @if($activeTab === 'posts')
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">My Posts</h2>
            <div class="flex space-x-2">
                <button wire:click="setPostsFilter('all')"
                        class="px-3 py-1 text-sm rounded-md {{ $postsFilter === 'all' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                    All
                </button>
                <button wire:click="setPostsFilter('published')"
                        class="px-3 py-1 text-sm rounded-md {{ $postsFilter === 'published' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                    Published
                </button>
                <button wire:click="setPostsFilter('draft')"
                        class="px-3 py-1 text-sm rounded-md {{ $postsFilter === 'draft' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                    Drafts
                </button>
                <button wire:click="setPostsFilter('scheduled')"
                        class="px-3 py-1 text-sm rounded-md {{ $postsFilter === 'scheduled' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                    Scheduled
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Title
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Category
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Stats
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @forelse($userPosts as $post)
                <tr>
                    <td class="px-6 py-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $post->title }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $post->created_at->format('M j, Y') }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $post->category->color ?? 'blue' }}-100 text-{{ $post->category->color ?? 'blue' }}-800 dark:bg-{{ $post->category->color ?? 'blue' }}-900 dark:text-{{ $post->category->color ?? 'blue' }}-200">
                            {{ $post->category->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $post->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($post->status === 'draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                            {{ ucfirst($post->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex items-center space-x-4">
                            <span title="Views">👁 {{ number_format($post->views_count) }}</span>
                            <span title="Likes">❤️ {{ number_format($post->likes_count) }}</span>
                            <span title="Comments">💬 {{ number_format($post->comments_count) }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium">
                        <div class="flex items-center space-x-3">
                            @if($post->status === 'published')
                            <a href="{{ route('posts.show', $post->slug) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                            @endif
                            <a href="{{ route('posts.edit', $post->slug) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</a>
                            <button wire:click="deletePost({{ $post->id }})"
                                    wire:confirm="Are you sure you want to delete this post?"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        No posts found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($userPosts->hasPages())
    <div class="mt-4">
        {{ $userPosts->links() }}
    </div>
    @endif
    @endif

    <!-- Bookmarks Tab -->
    @if($activeTab === 'bookmarks')
    <div class="mb-4">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">My Bookmarks</h2>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($bookmarkedPosts as $post)
        <article class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            @if($post->featured_image)
            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="object-cover w-full h-48">
            @else
            <div class="w-full h-48 bg-gradient-to-br from-blue-500 to-purple-600"></div>
            @endif

            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $post->category->color ?? 'blue' }}-100 text-{{ $post->category->color ?? 'blue' }}-800 dark:bg-{{ $post->category->color ?? 'blue' }}-900 dark:text-{{ $post->category->color ?? 'blue' }}-200">
                        {{ $post->category->name }}
                    </span>
                    @if($post->is_premium)
                    <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-200">
                        Premium
                    </span>
                    @endif
                </div>

                <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white line-clamp-2">
                    <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                        {{ $post->title }}
                    </a>
                </h3>

                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                    {{ $post->excerpt }}
                </p>

                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <img src="{{ $post->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) }}"
                             alt="{{ $post->author->name }}"
                             class="w-8 h-8 rounded-full">
                        <div class="text-sm">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $post->author->name }}</p>
                            <p class="text-gray-500 dark:text-gray-400">{{ $post->published_at->format('M j') }}</p>
                        </div>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $post->read_time }} min
                    </span>
                </div>
            </div>
        </article>
        @empty
        <div class="col-span-full py-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No bookmarks yet</h3>
            <p class="mt-1 text-gray-500 dark:text-gray-400">Start saving articles you want to read later</p>
            <div class="mt-6">
                <a href="{{ route('posts.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Browse Articles
                </a>
            </div>
        </div>
        @endforelse
    </div>

    @if($bookmarkedPosts->hasPages())
    <div class="mt-6">
        {{ $bookmarkedPosts->links() }}
    </div>
    @endif
    @endif
</div>
