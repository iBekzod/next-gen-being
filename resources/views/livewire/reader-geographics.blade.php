<div class="space-y-6">
    <!-- Header with Metrics Toggle -->
    <div class="flex items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span>📊</span> Reader Geographic Analytics
        </h3>
        <div class="flex gap-2">
            <button
                wire:click="setMetric('readers')"
                :class="$wire.selectedMetric === 'readers' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-slate-700 text-gray-900 dark:text-gray-300'"
                class="px-4 py-2 rounded-lg font-medium transition"
            >
                All Readers
            </button>
            <button
                wire:click="setMetric('authenticated')"
                :class="$wire.selectedMetric === 'authenticated' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-slate-700 text-gray-900 dark:text-gray-300'"
                class="px-4 py-2 rounded-lg font-medium transition"
            >
                Members
            </button>
        </div>
    </div>

    <!-- Today's Stats -->
    @if(!empty($analytics))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today's Readers</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $analytics['total_today'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">unique visitors</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Registered</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $analytics['authenticated_today'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">members</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Anonymous</p>
            <p class="text-3xl font-bold text-amber-600 dark:text-amber-400 mt-2">{{ $analytics['anonymous_today'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">guests</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Countries</p>
            <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $analytics['countries_count'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">locations</p>
        </div>
    </div>
    @endif

    <!-- Countries Breakdown Table -->
    @if(count($topCountries) > 0)
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Readers by Country</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Country</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Readers</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Percentage</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Trend</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                    @php
                        $totalReaders = array_sum(array_column($topCountries, 'readers'));
                    @endphp
                    @foreach($topCountries as $index => $country)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ $country['flag'] }}</span>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $country['country'] }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $country['code'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $country['readers'] }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                {{ round(($country['readers'] / max($totalReaders, 1)) * 100) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="w-24 bg-gray-200 dark:bg-slate-700 rounded-full h-2">
                                <div
                                    class="bg-blue-600 h-2 rounded-full"
                                    style="width: {{ round(($country['readers'] / max($totalReaders, 1)) * 100) }}%"
                                ></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-12 bg-gray-50 dark:bg-slate-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-slate-600">
        <p class="text-gray-600 dark:text-gray-400">
            <span class="text-3xl">📍</span><br>
            Geographic data will appear as readers from different countries visit your post
        </p>
    </div>
    @endif

    <!-- Insights -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
            <span>💡</span> Geographic Insights
        </h4>
        <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
            @if($readerBreakdown['total'] > 0)
                <li>✓ Your content is reaching <strong>{{ count($topCountries) }}</strong> countries worldwide</li>
                <li>✓ <strong>{{ round(($readerBreakdown['authenticated'] / $readerBreakdown['total']) * 100) }}%</strong> are registered members, showing strong community engagement</li>
                @if(count($topCountries) > 0)
                    <li>✓ Most readers are from <strong>{{ $topCountries[0]['country'] }}</strong></li>
                @endif
                <li>✓ Continue creating quality content to expand your global audience</li>
            @else
                <li>📖 Insights will appear as readers engage with your post</li>
            @endif
        </ul>
    </div>
</div>
