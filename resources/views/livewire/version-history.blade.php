<div class="space-y-6">
    <!-- Version History Header -->
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <span>📜</span> Version History
            @if(count($versions) > 0)
                <span class="ml-2 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-semibold rounded-full">
                    {{ count($versions) }}
                </span>
            @endif
        </h3>
    </div>

    <!-- Versions List -->
    @if (count($versions) > 0)
    <div class="space-y-3">
        @foreach ($versions as $version)
        <div
            wire:click="selectVersion({{ $version['id'] }})"
            class="p-4 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition"
            :class="{ 'ring-2 ring-blue-500': $wire.selectedVersionId === {{ $version['id'] }} }"
        >
            <!-- Version Header -->
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    @if ($version['editor_avatar'])
                        <img src="{{ $version['editor_avatar'] }}" alt="{{ $version['editor_name'] }}" class="w-10 h-10 rounded-full">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-sm font-bold">
                            {{ substr($version['editor_name'], 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $version['editor_name'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $version['created_at'] }}</p>
                    </div>
                </div>

                <!-- Change Type Badge -->
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @switch($version['change_type'])
                        @case('published') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 @break
                        @case('scheduled') bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 @break
                        @case('auto_save') bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @break
                        @default bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 @break
                    @endswitch
                ">
                    @switch($version['change_type'])
                        @case('published') 🚀 Published @break
                        @case('scheduled') 📅 Scheduled @break
                        @case('auto_save') 💾 Auto-saved @break
                        @default 💾 {{ ucfirst(str_replace('_', ' ', $version['change_type'])) }} @break
                    @endswitch
                </span>
            </div>

            <!-- Change Summary -->
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $version['change_summary'] }}</p>

            <!-- Content Preview -->
            <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded border border-gray-200 dark:border-slate-600 text-sm text-gray-700 dark:text-gray-300">
                <p class="line-clamp-2">{{ $version['content_preview'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 bg-gray-50 dark:bg-slate-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-slate-600">
        <p class="text-gray-600 dark:text-gray-400">No version history available yet.</p>
    </div>
    @endif

    <!-- Version Details Panel -->
    @if (!empty($versionDetails))
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Details Column -->
        <div class="lg:col-span-2 space-y-4">
            <div class="p-6 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Version Details</h4>

                <!-- Version Info -->
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Editor</p>
                        <div class="flex items-center gap-2 mt-1">
                            @if ($versionDetails['editor_avatar'])
                                <img src="{{ $versionDetails['editor_avatar'] }}" alt="{{ $versionDetails['editor_name'] }}" class="w-6 h-6 rounded-full">
                            @endif
                            <span class="text-gray-900 dark:text-white">{{ $versionDetails['editor_name'] }}</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Created</p>
                        <p class="text-gray-900 dark:text-white">{{ $versionDetails['created_at'] }}</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Change Summary</p>
                        <p class="text-gray-900 dark:text-white">{{ $versionDetails['change_summary'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Title -->
            <div class="p-6 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Title</h4>
                <p class="text-gray-700 dark:text-gray-300 text-lg">{{ $versionDetails['title'] }}</p>
            </div>

            <!-- Content Preview -->
            <div class="p-6 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Content</h4>
                <div class="prose dark:prose-invert max-w-none line-clamp-6 text-sm">
                    {!! Str::limit(strip_tags($versionDetails['full_content']), 500) !!}
                </div>
            </div>
        </div>

        <!-- Actions Column -->
        <div class="space-y-3">
            <div class="sticky top-4 space-y-3">
                @if ($canRestoreVersion())
                    <button
                        wire:click="restoreVersion({{ $versionDetails['id'] }})"
                        wire:confirm="Restore this version? This will create a new version with this content."
                        class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center justify-center gap-2"
                    >
                        <span>↩️</span>
                        <span>Restore This Version</span>
                    </button>
                @endif

                <button
                    class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition flex items-center justify-center gap-2"
                    disabled
                >
                    <span>👀</span>
                    <span>View Full Content</span>
                </button>

                <!-- Version Comparison (Placeholder) -->
                <div class="p-4 bg-gray-50 dark:bg-slate-700/50 rounded-lg border border-gray-200 dark:border-slate-600">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quick Stats</p>
                    <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                        <p>Type: <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $versionDetails['change_type'])) }}</span></p>
                        <p>Status: <span class="font-medium text-gray-900 dark:text-white">{{ $versionDetails['is_resolved'] ?? 'Saved' }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-8 text-gray-600 dark:text-gray-400">
        <p>Select a version to view details</p>
    </div>
    @endif
</div>
