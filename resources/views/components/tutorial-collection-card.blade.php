@props(['tutorial'])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden">
    <!-- Header -->
    <div class="p-6">
        <div class="flex items-start justify-between mb-2">
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $tutorial->title }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $tutorial->description }}
                </p>
            </div>

            <!-- Status Badge -->
            @if ($tutorial->status === 'published')
                <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900 px-3 py-1 text-xs font-medium text-green-800 dark:text-green-200 whitespace-nowrap ml-4">
                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Published
                </span>
            @elseif ($tutorial->status === 'review')
                <span class="inline-flex items-center rounded-full bg-yellow-100 dark:bg-yellow-900 px-3 py-1 text-xs font-medium text-yellow-800 dark:text-yellow-200 whitespace-nowrap ml-4">
                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Under Review
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-xs font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap ml-4">
                    Draft
                </span>
            @endif
        </div>

        <!-- Metadata -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 my-4 py-4 border-y border-gray-200 dark:border-gray-700">
            <div>
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Skill Level</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white capitalize mt-1">
                    {{ $tutorial->skill_level }}
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Duration</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                    ~{{ $tutorial->estimated_hours }} hour{{ $tutorial->estimated_hours !== 1 ? 's' : '' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Sources</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                    {{ count($tutorial->source_ids ?? []) }} source{{ count($tutorial->source_ids ?? []) !== 1 ? 's' : '' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Articles</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                    {{ count($tutorial->collected_content_ids ?? []) }} article{{ count($tutorial->collected_content_ids ?? []) !== 1 ? 's' : '' }}
                </p>
            </div>
        </div>

        <!-- Content Overview -->
        <div class="space-y-3 mb-4">
            @if (!empty($tutorial->steps))
                <div>
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">
                        {{ count($tutorial->steps) }} Steps
                    </h4>
                    <ol class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                        @foreach (array_slice($tutorial->steps, 0, 3) as $step)
                            <li class="flex items-start">
                                <span class="mr-2 font-semibold">{{ $step['step_num'] }}.</span>
                                <span>{{ substr($step['title'], 0, 50) }}{{ strlen($step['title']) > 50 ? '...' : '' }}</span>
                            </li>
                        @endforeach
                        @if (count($tutorial->steps) > 3)
                            <li class="text-blue-600 dark:text-blue-400 font-medium">
                                + {{ count($tutorial->steps) - 3 }} more steps
                            </li>
                        @endif
                    </ol>
                </div>
            @endif

            @if (!empty($tutorial->best_practices))
                <div>
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">
                        {{ count($tutorial->best_practices) }} Best Practices
                    </h4>
                </div>
            @endif

            @if (!empty($tutorial->common_pitfalls))
                <div>
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">
                        ⚠️ {{ count($tutorial->common_pitfalls) }} Common Pitfall{{ count($tutorial->common_pitfalls) !== 1 ? 's' : '' }}
                    </h4>
                </div>
            @endif

            @if (!empty($tutorial->code_examples))
                <div>
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">
                        Code Examples
                    </h4>
                    <div class="flex flex-wrap gap-1">
                        @foreach (array_unique(array_column($tutorial->code_examples, 'language')) as $lang)
                            <span class="inline-flex items-center rounded bg-gray-100 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ ucfirst($lang) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between">
        <div class="text-xs text-gray-600 dark:text-gray-400">
            Created {{ $tutorial->created_at->diffForHumans() }}
        </div>
        @if ($tutorial->status === 'published')
            <a href="#" class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                Read Tutorial
                <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Not yet available
            </span>
        @endif
    </div>
</div>
