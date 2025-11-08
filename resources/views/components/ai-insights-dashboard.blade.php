@props(['insights' => null, 'user' => null])

@php
    if (!$insights || !$user) {
        return;
    }

    $streak = $insights['learning_streak'] ?? [];
    $velocity = $insights['learning_velocity'] ?? [];
    $skillProgression = $insights['skill_progression'] ?? [];
    $patterns = $insights['learning_patterns'] ?? [];
    $milestones = $insights['next_milestones'] ?? [];
    $recSummary = $insights['recommendations_summary'] ?? [];
@endphp

<div class="space-y-6">
    <!-- Main Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Learning Streak -->
        <div class="rounded-xl bg-gradient-to-br from-orange-50 to-red-50 dark:from-slate-800 dark:to-slate-900 border border-orange-200 dark:border-slate-700 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Learning Streak</p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $streak['current'] ?? 0 }}</p>
                </div>
                <span class="text-3xl">🔥</span>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $streak['status'] ?? 'No streak yet' }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">Best: {{ $streak['best'] ?? 0 }} days</p>
        </div>

        <!-- Productivity Score -->
        <div class="rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 border border-blue-200 dark:border-slate-700 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Productivity</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $insights['productivity_score'] ?? 0 }}</p>
                </div>
                <span class="text-3xl">⚡</span>
            </div>
            <div class="w-full h-2 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500"
                     style="width: {{ $insights['productivity_score'] ?? 0 }}%"></div>
            </div>
        </div>

        <!-- Consistency -->
        <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-slate-800 dark:to-slate-900 border border-emerald-200 dark:border-slate-700 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Consistency</p>
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $insights['consistency_score'] ?? 0 }}%</p>
                </div>
                <span class="text-3xl">📈</span>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-300">Last 30 days</p>
        </div>

        <!-- Weekly Velocity -->
        <div class="rounded-xl bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-800 dark:to-slate-900 border border-purple-200 dark:border-slate-700 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">This Week</p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $velocity['this_week'] ?? 0 }}</p>
                </div>
                <span class="text-3xl">🚀</span>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-300">Parts completed</p>
        </div>
    </div>

    <!-- Skills & Patterns Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Skill Progression -->
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-6 shadow-lg">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>📊</span>
                Skill Progression
            </h3>
            <div class="space-y-3">
                @php
                    $levels = ['beginner' => ['label' => 'Beginner', 'color' => 'blue'], 'intermediate' => ['label' => 'Intermediate', 'color' => 'indigo'], 'advanced' => ['label' => 'Advanced', 'color' => 'orange'], 'expert' => ['label' => 'Expert', 'color' => 'red']];
                    $distribution = $skillProgression['distribution'] ?? [];
                    $maxCount = max(array_values($distribution), 1);
                @endphp
                @foreach($levels as $key => $level)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $level['label'] }}</span>
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-400">{{ $distribution[$key] ?? 0 }}</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r transition-all"
                             style="width: {{ $maxCount > 0 ? (($distribution[$key] ?? 0) / $maxCount) * 100 : 0 }}%;
                                     background: linear-gradient(to right, {{ match($level['color']) {
                                         'blue' => '#3b82f6, #1e40af',
                                         'indigo' => '#6366f1, #312e81',
                                         'orange' => '#f97316, #c2410c',
                                         'red' => '#ef4444, #991b1b',
                                     } }})"></div>
                    </div>
                </div>
                @endforeach
                <div class="pt-2 border-t border-gray-200 dark:border-slate-700 mt-4">
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Current Level: <span class="text-blue-600 dark:text-blue-400">{{ $skillProgression['current_level'] ?? 'Getting Started' }}</span></p>
                </div>
            </div>
        </div>

        <!-- Learning Patterns -->
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-6 shadow-lg">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>🎯</span>
                Learning Patterns
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Preferred Time</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $patterns['preferred_time_of_day'] ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Average Session</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $patterns['average_session_duration_minutes'] ?? 0 }} min</p>
                </div>
                <div class="pt-2 border-t border-gray-200 dark:border-slate-700">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $patterns['optimal_study_time'] ?? 'Start tracking your learning time' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Milestones & Recommendations -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Next Milestones -->
        @if(!empty($milestones))
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-6 shadow-lg">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>🎁</span>
                Upcoming Milestones
            </h3>
            <div class="space-y-3">
                @foreach($milestones as $milestone)
                <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg border border-gray-200 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $milestone['name'] }}</span>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $milestone['progress'] }}%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all"
                             style="width: {{ $milestone['progress'] }}%"></div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">{{ $milestone['remaining'] }} items remaining</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recommendations Impact -->
        <div class="rounded-xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-slate-800 dark:to-slate-900 border border-amber-200 dark:border-slate-700 p-6 shadow-lg">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>💡</span>
                AI Recommendations
            </h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-1">Active Recommendations</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $recSummary['total_active'] ?? 0 }}</p>
                </div>
                <div class="pt-3 border-t border-amber-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-1">Engagement Rate</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $recSummary['engagement_rate'] ?? '0%' }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">{{ $recSummary['acted_on'] ?? 0 }} recommendations acted on</p>
                </div>
            </div>
        </div>
    </div>
</div>
