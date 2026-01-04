@props(['post', 'format' => 'inline'])

@if ($post->sourceReferences->isEmpty())
    <div class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No sources</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">No references found for this post.</p>
    </div>
@else
<div class="space-y-6">
    <!-- References Header -->
    <div class="border-t pt-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
            Sources & References
        </h2>

        <!-- Filter/Format Options -->
        <div class="flex items-center space-x-4 mb-6">
            <span class="text-sm text-gray-600 dark:text-gray-400">Format:</span>
            <div class="flex space-x-2">
                <button
                    @if ($format === 'inline') disabled @endif
                    class="px-3 py-1 text-xs font-medium rounded-full
                    @if ($format === 'inline')
                        bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                    @else
                        bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600
                    @endif">
                    Inline [1]
                </button>
                <button
                    @if ($format === 'apa') disabled @endif
                    class="px-3 py-1 text-xs font-medium rounded-full
                    @if ($format === 'apa')
                        bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                    @else
                        bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600
                    @endif">
                    APA
                </button>
                <button
                    @if ($format === 'chicago') disabled @endif
                    class="px-3 py-1 text-xs font-medium rounded-full
                    @if ($format === 'chicago')
                        bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                    @else
                        bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600
                    @endif">
                    Chicago
                </button>
                <button
                    @if ($format === 'harvard') disabled @endif
                    class="px-3 py-1 text-xs font-medium rounded-full
                    @if ($format === 'harvard')
                        bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                    @else
                        bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600
                    @endif">
                    Harvard
                </button>
            </div>
        </div>

        <!-- Sources by Domain -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Sources by Website ({{ $post->sourceReferences->unique('domain')->count() }})
            </h3>
            <div class="space-y-3">
                @foreach ($post->sourceReferences->unique('domain') as $domain => $refs)
                    @php
                        $domainRefs = $post->sourceReferences->where('domain', $refs->first()->domain)->values();
                    @endphp
                    <details class="group">
                        <summary class="flex cursor-pointer items-center justify-between bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M12.586 4.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM12.586 4.586l2.828 2.828m0 0L8.172 15.172m0 0l2.828-2.828m-2.828 2.828l-2.83-2.83m0 0L2 17.586M2 5.586l5.414 5.414" />
                                </svg>
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $refs->first()->domain }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $domainRefs->count() }} reference(s)
                                    </div>
                                </div>
                            </div>
                            <svg class="h-5 w-5 text-gray-500 group-open:rotate-180 transition-transform"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </summary>
                        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 space-y-2 mt-1 rounded-lg">
                            @foreach ($domainRefs as $reference)
                                <div class="space-y-1">
                                    <a href="{{ $reference->url }}" target="_blank" rel="noopener noreferrer"
                                       class="block text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline font-medium">
                                        {{ $reference->title }}
                                    </a>
                                    @if ($reference->author)
                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                            by {{ $reference->author }}
                                        </div>
                                    @endif
                                    @if ($reference->published_at)
                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                            Published: {{ $reference->published_at->format('M d, Y') }}
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-500 dark:text-gray-500 pt-1">
                                        Citation: <span class="font-mono text-xs bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded">{{ $reference->citation_style }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </div>

        <!-- Full References List -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                All References ({{ $post->sourceReferences->count() }})
            </h3>
            <ol class="space-y-3 list-decimal list-inside">
                @foreach ($post->sourceReferences->sortBy('position_in_post') as $index => $reference)
                    <li class="text-gray-700 dark:text-gray-300">
                        <a href="{{ $reference->url }}" target="_blank" rel="noopener noreferrer"
                           class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                            {{ $reference->title }}
                        </a>
                        @if ($reference->author)
                            <span class="text-gray-600 dark:text-gray-400">
                                — {{ $reference->author }}
                            </span>
                        @endif
                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 ml-5">
                            {{ $reference->domain }}
                            @if ($reference->published_at)
                                • {{ $reference->published_at->format('M d, Y') }}
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        <!-- Share References -->
        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Share Citation
                </h3>
                <div class="flex space-x-2">
                    <button class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                            onclick="copyToClipboard('bibtex')">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copy BibTeX
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
