<div class="space-y-6">
    <!-- Comments Header -->
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <span>💬</span> Editorial Comments
            @if(count($comments) > 0)
                <span class="ml-2 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-semibold rounded-full">
                    {{ count($comments) }}
                </span>
            @endif
        </h3>

        @if ($canAddComments())
            <button
                wire:click="$toggle('showCommentForm')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm"
            >
                <span>➕</span>
                <span>{{ $showCommentForm ? 'Cancel' : 'Add Comment' }}</span>
            </button>
        @endif
    </div>

    <!-- Add Comment Form -->
    @if ($canAddComments() && $showCommentForm)
    <div class="p-6 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700">
        <form wire:submit="addComment" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Comment</label>
                <textarea
                    wire:model="newCommentContent"
                    placeholder="Add an editorial comment or suggestion..."
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                ></textarea>
                @error('newCommentContent')
                    <span class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Section (Optional)</label>
                <input
                    type="text"
                    wire:model="selectedSection"
                    placeholder="e.g., Introduction, Section 2, etc."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>

            <div class="flex gap-3 justify-end">
                <button
                    type="button"
                    wire:click="$set('showCommentForm', false)"
                    class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
                >
                    Post Comment
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Comments List -->
    @if (count($comments) > 0)
        <div class="space-y-4">
            @foreach ($comments as $comment)
                <div class="p-4 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 space-y-3">
                    <!-- Comment Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            @if ($comment['user_avatar'])
                                <img src="{{ $comment['user_avatar'] }}" alt="{{ $comment['user_name'] }}" class="w-8 h-8 rounded-full">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold">
                                    {{ substr($comment['user_name'], 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $comment['user_name'] }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $comment['created_at'] }}</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($comment['is_resolved']) bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                            @elseif($comment['status'] === 'needs_discussion') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200
                            @else bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 @endif
                        ">
                            @if($comment['is_resolved']) ✓ Resolved @else 🔵 {{ ucfirst(str_replace('_', ' ', $comment['status'])) }} @endif
                        </span>
                    </div>

                    <!-- Section Info -->
                    @if ($comment['section'])
                    <div class="inline-block px-2 py-1 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 text-xs rounded">
                        📍 {{ $comment['section'] }}
                    </div>
                    @endif

                    <!-- Comment Content -->
                    <p class="text-gray-700 dark:text-gray-300">{{ $comment['content'] }}</p>

                    <!-- Replies -->
                    @if (count($comment['replies']) > 0)
                        <div class="ml-6 space-y-3 pt-3 border-t border-gray-200 dark:border-slate-700 mt-3">
                            @foreach ($comment['replies'] as $reply)
                                <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded">
                                    <div class="flex items-center gap-2 mb-2">
                                        @if ($reply['user_avatar'])
                                            <img src="{{ $reply['user_avatar'] }}" alt="{{ $reply['user_name'] }}" class="w-6 h-6 rounded-full">
                                        @else
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold">
                                                {{ substr($reply['user_name'], 0, 1) }}
                                            </div>
                                        @endif
                                        <span class="font-medium text-sm text-gray-900 dark:text-white">{{ $reply['user_name'] }}</span>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ $reply['created_at'] }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $reply['content'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex gap-2 pt-3 border-t border-gray-200 dark:border-slate-700">
                        @if ($canAddComments())
                            <button
                                wire:click="$set('replyingTo', {{ $comment['id'] }})"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                            >
                                💬 Reply
                            </button>
                        @endif

                        @if ($comment['can_resolve'] && !$comment['is_resolved'])
                            <button
                                wire:click="resolveComment({{ $comment['id'] }})"
                                class="text-sm text-green-600 dark:text-green-400 hover:underline"
                            >
                                ✓ Resolve
                            </button>
                        @endif
                    </div>

                    <!-- Reply Form -->
                    @if ($replyingTo === $comment['id'] && $canAddComments())
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-slate-700">
                        <div class="flex gap-2">
                            <input
                                type="text"
                                wire:model="replyContent"
                                placeholder="Write a reply..."
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-slate-600 rounded dark:bg-slate-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <button
                                wire:click="addReply({{ $comment['id'] }})"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm transition"
                            >
                                Send
                            </button>
                            <button
                                wire:click="$set('replyingTo', null)"
                                class="px-3 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 rounded text-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 bg-gray-50 dark:bg-slate-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-slate-600">
            <p class="text-gray-600 dark:text-gray-400">
                @if ($canAddComments())
                    No editorial comments yet. Start a discussion!
                @else
                    No editorial comments on this post.
                @endif
            </p>
        </div>
    @endif
</div>
