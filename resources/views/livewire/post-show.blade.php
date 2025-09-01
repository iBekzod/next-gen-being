<article class="max-w-4xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
    <!-- Header -->
    <header class="mb-8">
        <div class="flex items-center mb-4 space-x-3">
            <span class="px-3 py-1 bg-{{ $post->category->color ?? 'blue' }}-500 text-white text-sm font-medium rounded-full">
                {{ $post->category->name }}
            </span>
            @if($post->is_premium)
            <span class="flex items-center px-3 py-1 text-sm font-medium text-white rounded-full bg-gradient-to-r from-yellow-400 to-orange-500">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Premium Content
            </span>
            @endif
            <span class="text-sm text-gray-500">{{ $post->read_time }} min read</span>
        </div>

        <h1 class="mb-4 text-3xl font-bold text-gray-900 lg:text-4xl">{{ $post->title }}</h1>
        <p class="mb-6 text-xl text-gray-600">{{ $post->excerpt }}</p>

        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <img src="{{ $post->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) }}"
                     alt="{{ $post->author->name }}"
                     class="w-12 h-12 rounded-full">
                <div>
                    <p class="font-semibold text-gray-900">{{ $post->author->name }}</p>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <span>{{ $post->published_at->format('M j, Y') }}</span>
                        <span>•</span>
                        <span>{{ number_format($post->views_count) }} views</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-3">
                <button wire:click="toggleLike"
                        class="flex items-center space-x-2 px-4 py-2 rounded-lg border {{ auth()->user()?->hasLiked($post) ? 'bg-red-50 border-red-200 text-red-600' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }} transition-colors">
                    <svg class="w-5 h-5" fill="{{ auth()->user()?->hasLiked($post) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span>{{ number_format($post->likes_count) }}</span>
                </button>

                <button wire:click="toggleBookmark"
                        class="flex items-center space-x-2 px-4 py-2 rounded-lg border {{ auth()->user()?->hasBookmarked($post) ? 'bg-blue-50 border-blue-200 text-blue-600' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }} transition-colors">
                    <svg class="w-5 h-5" fill="{{ auth()->user()?->hasBookmarked($post) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </button>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center px-4 py-2 space-x-2 text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                        </svg>
                    </button>
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 z-10 w-48 py-2 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg">
                        <button class="w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50">
                            Copy Link
                        </button>
                        <button class="w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50">
                            Share on Twitter
                        </button>
                        <button class="w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50">
                            Share on LinkedIn
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Featured Image -->
    @if($post->featured_image)
    <div class="mb-8">
        <img src="{{ $post->featured_image }}"
             alt="{{ $post->title }}"
             class="object-cover w-full h-96 rounded-2xl">
    </div>
    @endif

    <!-- Content -->
    <div class="mb-12 prose prose-lg max-w-none">
        {!! $post->content !!}
    </div>

    <!-- Tags -->
    @if($post->tags->count() > 0)
    <div class="flex items-center mb-8 space-x-2">
        <span class="font-medium text-gray-700">Tags:</span>
        @foreach($post->tags as $tag)
        <a href="{{ route('posts.index', ['tag' => $tag->slug]) }}"
           class="px-3 py-1 text-sm text-gray-700 transition-colors bg-gray-100 rounded-full hover:bg-gray-200">
            {{ $tag->name }}
        </a>
        @endforeach
    </div>
    @endif

    <!-- Comments Section -->
    @if($post->allow_comments)
    <section class="pt-8 border-t border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                Comments ({{ $comments->count() }})
            </h2>
            @auth
            <button wire:click="$set('showCommentForm', true)"
                    class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                Add Comment
            </button>
            @endauth
        </div>

        <!-- Comment Form -->
        @if($showCommentForm)
        <div class="p-6 mb-8 rounded-lg bg-gray-50">
            @if($replyingTo)
            <div class="p-3 mb-4 bg-white border-l-4 border-blue-500 rounded">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900">Replying to {{ $replyingTo->user->name }}</span>
                    <button wire:click="cancelReply" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 line-clamp-2">{{ $replyingTo->content }}</p>
            </div>
            @endif

            <form wire:submit="submitComment">
                <div class="mb-4">
                    <textarea wire:model="commentContent"
                              rows="4"
                              placeholder="Write your comment..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    @error('commentContent')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">Your comment will be reviewed before being published.</p>
                    <div class="flex space-x-3">
                        <button type="button"
                                wire:click="$set('showCommentForm', false)"
                                class="px-4 py-2 text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                            Post Comment
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        <!-- Comments List -->
        <div class="space-y-6">
            @foreach($comments as $comment)
            <div class="flex space-x-4">
                <img src="{{ $comment->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name) }}"
                     alt="{{ $comment->user->name }}"
                     class="flex-shrink-0 w-10 h-10 rounded-full">
                <div class="flex-1">
                    <div class="p-4 rounded-lg bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900">{{ $comment->user->name }}</h4>
                            <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-700">{{ $comment->content }}</p>
                    </div>

                    <div class="flex items-center mt-2 space-x-4">
                        <button class="text-sm text-gray-500 transition-colors hover:text-gray-700">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            {{ $comment->likes_count }}
                        </button>
                        @auth
                        <button wire:click="replyTo({{ $comment->id }})"
                                class="text-sm text-gray-500 transition-colors hover:text-gray-700">
                            Reply
                        </button>
                        @endauth
                    </div>

                    <!-- Replies -->
                    @if($comment->approvedReplies->count() > 0)
                    <div class="mt-4 space-y-4">
                        @foreach($comment->approvedReplies as $reply)
                        <div class="flex space-x-3">
                            <img src="{{ $reply->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($reply->user->name) }}"
                                 alt="{{ $reply->user->name }}"
                                 class="flex-shrink-0 w-8 h-8 rounded-full">
                            <div class="flex-1">
                                <div class="p-3 bg-white border border-gray-200 rounded-lg">
                                    <div class="flex items-center justify-between mb-1">
                                        <h5 class="text-sm font-medium text-gray-900">{{ $reply->user->name }}</h5>
                                        <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700">{{ $reply->content }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @guest
        <div class="py-8 mt-8 text-center border-t border-gray-200">
            <p class="mb-4 text-gray-600">Please log in to leave a comment.</p>
            <a href="{{ route('login') }}"
               class="inline-flex items-center px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                Log In
            </a>
        </div>
        @endguest
    </section>
    @endif

    <!-- Related Posts -->
    @if($relatedPosts->count() > 0)
    <section class="pt-8 mt-12 border-t border-gray-200">
        <h2 class="mb-6 text-2xl font-bold text-gray-900">Related Articles</h2>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach($relatedPosts as $relatedPost)
            <article class="group">
                <div class="mb-4 aspect-w-16 aspect-h-9">
                    @if($relatedPost->featured_image)
                    <img src="{{ $relatedPost->featured_image }}"
                         alt="{{ $relatedPost->title }}"
                         class="object-cover w-full h-32 transition-opacity rounded-lg group-hover:opacity-90">
                    @else
                    <div class="w-full h-32 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600"></div>
                    @endif
                </div>
                <h3 class="font-semibold text-gray-900 transition-colors group-hover:text-blue-600 line-clamp-2">
                    <a href="{{ route('posts.show', $relatedPost->slug) }}">{{ $relatedPost->title }}</a>
                </h3>
                <p class="mt-1 text-sm text-gray-500">{{ $relatedPost->published_at->format('M j, Y') }}</p>
            </article>
            @endforeach
        </div>
    </section>
    @endif
</article>
