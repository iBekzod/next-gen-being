@props(['recommendations' => null, 'limit' => 5])

@php
    if (!$recommendations) {
        return;
    }

    $recommendations = is_array($recommendations) ? collect($recommendations) : $recommendations;
@endphp

<div class="rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-700 dark:to-slate-700 border-b border-gray-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="text-2xl">💡</span>
                AI-Powered Recommendations
            </h2>
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300 rounded-full text-sm font-bold">
                {{ $recommendations->count() }}
            </span>
        </div>
    </div>

    <!-- Recommendations List -->
    @if($recommendations->isNotEmpty())
    <div class="divide-y divide-gray-200 dark:divide-slate-700">
        @foreach($recommendations->take($limit) as $recommendation)
        <div x-data="{ dismissed: false }"
             x-show="!dismissed"
             class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors"
             @dismiss-recommendation.window="if ($event.detail.id === {{ $recommendation->id }}) dismissed = true">

            <div class="flex gap-4">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    @php
                        $iconMap = [
                            'next_tutorial' => '📚',
                            'skill_gap' => '⬆️',
                            'related_content' => '🔗',
                            'challenge' => '💪',
                            'review' => '🔄',
                        ];
                    @endphp
                    <span class="text-2xl">{{ $iconMap[$recommendation->recommendation_type] ?? '💡' }}</span>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $recommendation->title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $recommendation->description }}</p>

                            @if($recommendation->reason)
                            <div class="mt-2 inline-flex items-center gap-1 px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded text-xs">
                                <span>💭</span>
                                <span>{{ $recommendation->reason }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Confidence Badge -->
                        <div class="flex-shrink-0 text-right">
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-full text-xs font-bold">
                                <span>✓</span>
                                <span>{{ $recommendation->getConfidencePercentage() }}% Match</span>
                            </span>
                        </div>
                    </div>

                    <!-- Type & Actions -->
                    <div class="mt-3 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded text-xs font-semibold">
                            {{ ucfirst(str_replace('_', ' ', $recommendation->recommendation_type)) }}
                        </span>

                        <div class="flex items-center gap-2">
                            @if($recommendation->post)
                            <a href="{{ route('posts.show', $recommendation->post) }}" class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold transition-colors">
                                <span>Start Learning</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            @endif

                            <button @click="
                                dismissed = true;
                                fetch(`/api/recommendations/{{ $recommendation->id }}/dismiss`, { method: 'POST' })
                            "
                                    class="inline-flex items-center gap-1 px-3 py-1 bg-gray-200 dark:bg-slate-700 hover:bg-gray-300 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-300 rounded text-xs font-semibold transition-colors"
                                    title="Not interested">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- View More -->
    @if($recommendations->count() > $limit)
    <div class="px-6 py-4 text-center border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
        <a href="{{ route('recommendations.index') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
            View All {{ $recommendations->count() }} Recommendations →
        </a>
    </div>
    @endif
    @else
    <div class="px-6 py-12 text-center">
        <p class="text-gray-600 dark:text-gray-400 mb-3">No recommendations yet</p>
        <p class="text-sm text-gray-500 dark:text-gray-500">Continue learning to get personalized recommendations</p>
    </div>
    @endif
</div>
