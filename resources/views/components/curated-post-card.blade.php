@props(['post'])

<article class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden">
    <!-- Header -->
    <div class="p-6">
        <div class="flex items-start justify-between mb-2">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    <a href="{{ route('posts.show', $post) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                        {{ $post->title }}
                    </a>
                </h2>
            </div>
            @if ($post->is_curated)
                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Curated
                </span>
            @endif
        </div>

        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $post->excerpt }}</p>

        <!-- Quality Metrics -->
        @if ($post->paraphrase_confidence_score)
            <div class="flex items-center space-x-4 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 mr-2">Confidence:</span>
                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $post->paraphrase_confidence_score * 100 }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 ml-2">
                        {{ (int) ($post->paraphrase_confidence_score * 100) }}%
                    </span>
                </div>

                @if ($post->is_fact_verified)
                    <div class="flex items-center text-green-600 dark:text-green-400">
                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-xs font-semibold">Fact Verified</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Sources Section -->
        @if ($post->sourceReferences->isNotEmpty())
            <div class="mb-4">
                <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                    Sources ({{ $post->sourceReferences->count() }})
                </h3>
                <div class="space-y-2">
                    @foreach ($post->sourceReferences->take(3) as $reference)
                        <a href="{{ $reference->url }}" target="_blank" rel="noopener noreferrer"
                           class="block text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            <div class="flex items-start space-x-2">
                                <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM15.657 14.243a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM11 17a1 1 0 102 0v-1a1 1 0 10-2 0v1zM5.757 15.657a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zM5.757 4.343a1 1 0 00-1.414 1.414l.707.707a1 1 0 001.414-1.414l-.707-.707z" />
                                </svg>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white truncate">
                                        {{ $reference->title }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $reference->domain }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($post->sourceReferences->count() > 3)
                    <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-2 font-semibold">
                        View all {{ $post->sourceReferences->count() }} sources
                    </button>
                @endif
            </div>
        @endif

        <!-- Language Badges -->
        @if ($post->base_post_id)
            <div class="mb-4">
                <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:text-blue-200">
                    Translation: {{ strtoupper($post->base_language) }}
                </span>
            </div>
        @else
            @php
                $translationCount = $post->translatedVersions()->count();
            @endphp
            @if ($translationCount > 0)
                <div class="mb-4">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 mr-2">Available in {{ $translationCount }} languages</span>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach ($post->translatedVersions()->limit(5)->get() as $translation)
                            <span class="inline-flex items-center rounded-full bg-purple-100 dark:bg-purple-900 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:text-purple-200">
                                {{ strtoupper($translation->base_language) }}
                            </span>
                        @endforeach
                        @if ($translationCount > 5)
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:text-gray-200">
                                +{{ $translationCount - 5 }} more
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between">
        <div class="text-xs text-gray-600 dark:text-gray-400">
            Published {{ $post->published_at?->diffForHumans() ?? 'as draft' }}
        </div>
        <a href="{{ route('posts.show', $post) }}"
           class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
            Read more
            <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>
</article>
