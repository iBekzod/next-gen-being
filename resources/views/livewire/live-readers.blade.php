<div class="space-y-6">
    <!-- Active Readers Counter -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-900 rounded-lg p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-blue-100">👥 Currently Reading</p>
                <p class="text-4xl font-bold mt-2">{{ $activeReaderCount }}</p>
                <p class="text-sm text-blue-100 mt-1">
                    @if($readerBreakdown['authenticated'] > 0)
                        {{ $readerBreakdown['authenticated'] }} registered · {{ $readerBreakdown['anonymous'] }} anonymous
                    @else
                        All readers are reading this amazing content!
                    @endif
                </p>
            </div>
            <div class="text-6xl opacity-20">👁️</div>
        </div>
    </div>

    <!-- Reader Breakdown -->
    @if($readerBreakdown['total'] > 0)
    <div class="grid grid-cols-3 gap-4">
        <!-- Total Readers -->
        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Readers</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $readerBreakdown['total'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Right now reading</p>
        </div>

        <!-- Authenticated Readers -->
        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">👤 Registered</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $readerBreakdown['authenticated'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                @if($readerBreakdown['total'] > 0)
                    {{ round(($readerBreakdown['authenticated'] / $readerBreakdown['total']) * 100) }}% of readers
                @endif
            </p>
        </div>

        <!-- Anonymous Readers -->
        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">🔍 Anonymous</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-2">{{ $readerBreakdown['anonymous'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                @if($readerBreakdown['total'] > 0)
                    {{ round(($readerBreakdown['anonymous'] / $readerBreakdown['total']) * 100) }}% of readers
                @endif
            </p>
        </div>
    </div>
    @endif

    <!-- Top Countries Reading -->
    @if(count($topCountries) > 0)
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span>🌍</span> Reading from {{ count($topCountries) }} Countries
            </h3>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-slate-700">
            @foreach($topCountries as $country)
            <div class="px-6 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ $country['flag'] }}</span>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $country['country'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $country['code'] }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-blue-600 dark:text-blue-400">{{ $country['readers'] }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">reader{{ $country['readers'] !== 1 ? 's' : '' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Live Readers List -->
    @if(count($liveReadersList) > 0)
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="animate-pulse">🔴</span> Live Readers Right Now
            </h3>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-slate-700 max-h-96 overflow-y-auto">
            @foreach($liveReadersList as $reader)
            <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                <div class="flex items-center gap-3">
                    @if($reader['avatar'])
                        <img src="{{ $reader['avatar'] }}" alt="{{ $reader['name'] }}" class="w-10 h-10 rounded-full">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-sm font-bold">
                            {{ substr($reader['name'], 0, 1) }}
                        </div>
                    @endif

                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $reader['name'] }}</p>
                            @if($reader['is_authenticated'])
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                    ✓ Member
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    Anonymous
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            Reading for {{ $reader['reading_duration'] }} • Active {{ $reader['last_activity'] }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-8 bg-gray-50 dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700">
        <p class="text-gray-600 dark:text-gray-400">
            <span class="text-2xl">📖</span><br>
            No one is reading this post right now. Be the first to share it!
        </p>
    </div>
    @endif
</div>
