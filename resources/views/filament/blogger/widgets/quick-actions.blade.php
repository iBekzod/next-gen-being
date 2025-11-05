<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Quick Actions
        </x-slot>

        <x-slot name="description">
            Common tasks to manage your content and grow your audience
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Create New Post -->
            <a href="{{ route('filament.blogger.resources.my-posts.create') }}"
               class="flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group">
                <div class="flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-primary-100 dark:bg-primary-900/20 group-hover:bg-primary-200 dark:group-hover:bg-primary-900/40 transition">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Create New Post</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-center">Write your next blog post</p>
            </a>

            <!-- Generate Videos -->
            <a href="{{ route('filament.blogger.resources.my-posts.index') }}"
               class="flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-success-500 dark:hover:border-success-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group">
                <div class="flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-success-100 dark:bg-success-900/20 group-hover:bg-success-200 dark:group-hover:bg-success-900/40 transition">
                    <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Generate Videos</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-center">
                    @if($postsReadyForVideo > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/20 dark:text-success-400">
                            {{ $postsReadyForVideo }} ready
                        </span>
                    @else
                        No posts ready
                    @endif
                </p>
            </a>

            <!-- Publish to Social -->
            <a href="{{ route('filament.blogger.resources.social-accounts.index') }}"
               class="flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-info-500 dark:hover:border-info-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group">
                <div class="flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-info-100 dark:bg-info-900/20 group-hover:bg-info-200 dark:group-hover:bg-info-900/40 transition">
                    <svg class="w-6 h-6 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Social Accounts</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-center">
                    @if($connectedAccounts > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900/20 dark:text-info-400">
                            {{ $connectedAccounts }} connected
                        </span>
                    @else
                        Connect platforms
                    @endif
                </p>
            </a>

            <!-- View Jobs -->
            <a href="{{ route('filament.blogger.resources.job-statuses.index') }}"
               class="flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-warning-500 dark:hover:border-warning-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group">
                <div class="flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-warning-100 dark:bg-warning-900/20 group-hover:bg-warning-200 dark:group-hover:bg-warning-900/40 transition">
                    <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">My Jobs</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-center">
                    @if($activeJobs > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/20 dark:text-warning-400">
                            {{ $activeJobs }} active
                        </span>
                    @elseif($failedJobs > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/20 dark:text-danger-400">
                            {{ $failedJobs }} failed
                        </span>
                    @else
                        All complete
                    @endif
                </p>
            </a>
        </div>

        <!-- Stats Summary -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $publishedPosts }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Published Posts</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $draftPosts }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Drafts</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $videosReadyToPublish }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Videos Ready</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $aiQuotaLimit === 'unlimited' ? '∞' : $aiQuotaUsed . '/' . $aiQuotaLimit }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">AI Quota</div>
                </div>
            </div>
        </div>

        <!-- Help Notice -->
        @if($connectedAccounts === 0 || $publishedPosts === 0)
        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Getting Started</h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <ol class="list-decimal list-inside space-y-1">
                            @if($publishedPosts === 0)
                            <li>Create and publish your first post</li>
                            @endif
                            @if($connectedAccounts === 0)
                            <li>Connect your social media accounts</li>
                            @endif
                            <li>Generate a video from your published post</li>
                            <li>Publish the video to your social media accounts</li>
                            <li>Track progress in the "My Jobs" section</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
