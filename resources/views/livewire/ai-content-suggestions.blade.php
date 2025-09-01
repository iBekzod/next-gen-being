<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">AI Content Suggestions</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                AI-generated content ideas based on trending topics and audience interests
            </p>
        </div>
        <button wire:click="generateSuggestions"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-blue-600 rounded-md hover:from-purple-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
            <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <svg wire:loading class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span wire:loading.remove>Generate New Suggestions</span>
            <span wire:loading>Generating...</span>
        </button>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap items-center gap-4">
        <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</label>
            <select wire:model.live="statusFilter" class="text-sm border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="all">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="used">Used</option>
            </select>
        </div>
        <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</label>
            <select wire:model.live="sortBy" class="text-sm border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="relevance">Relevance Score</option>
                <option value="created">Date Created</option>
                <option value="reviewed">Date Reviewed</option>
            </select>
        </div>
    </div>

    <!-- Suggestions List -->
    <div class="space-y-4">
        @forelse($suggestions as $suggestion)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $suggestion->title }}
                            </h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{
                                $suggestion->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                ($suggestion->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                ($suggestion->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'))
                            }}">
                                {{ ucfirst($suggestion->status) }}
                            </span>
                        </div>

                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            {{ $suggestion->description }}
                        </p>

                        <div class="flex flex-wrap gap-4 mb-4 text-sm">
                            @if($suggestion->topics && count($suggestion->topics) > 0)
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">Topics:</span>
                                @foreach($suggestion->topics as $topic)
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs dark:bg-blue-900 dark:text-blue-200">
                                    {{ $topic }}
                                </span>
                                @endforeach
                            </div>
                            @endif

                            @if($suggestion->keywords && count($suggestion->keywords) > 0)
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">Keywords:</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ implode(', ', array_slice($suggestion->keywords, 0, 5)) }}</span>
                                @if(count($suggestion->keywords) > 5)
                                <span class="text-gray-500 dark:text-gray-400">+{{ count($suggestion->keywords) - 5 }} more</span>
                                @endif
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center space-x-6 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span>Relevance: {{ number_format($suggestion->relevance_score * 100) }}%</span>
                            </div>

                            @if($suggestion->source_url)
                            <a href="{{ $suggestion->source_url }}" target="_blank" rel="noopener" class="flex items-center space-x-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                <span>Source</span>
                            </a>
                            @endif

                            <span>Suggested {{ $suggestion->created_at->diffForHumans() }}</span>

                            @if($suggestion->reviewed_at)
                            <span>Reviewed {{ $suggestion->reviewed_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center ml-4 space-x-2">
                        @if($suggestion->status === 'pending')
                        <button wire:click="approveSuggestion({{ $suggestion->id }})"
                                wire:loading.attr="disabled"
                                class="p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                        <button wire:click="rejectSuggestion({{ $suggestion->id }})"
                                wire:loading.attr="disabled"
                                class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        @endif

                        @if($suggestion->status === 'approved' && auth()->user()->hasAnyRole(['admin', 'content_manager', 'blogger']))
                        <a href="{{ route('posts.create', ['suggestion' => $suggestion->id]) }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Post
                        </a>
                        @endif
                    </div>
                </div>

                @if($suggestion->reviewedBy)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ ucfirst($suggestion->status) }} by
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $suggestion->reviewedBy->name }}</span>
                        {{ $suggestion->reviewed_at->diffForHumans() }}
                    </p>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No AI suggestions yet</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Click the "Generate New Suggestions" button to get AI-powered content ideas
            </p>
        </div>
        @endforelse
    </div>

    @if($suggestions->hasPages())
    <div class="mt-6">
        {{ $suggestions->links() }}
    </div>
    @endif
</div>
