@props(['user' => null, 'stats' => null, 'compact' => false])

@php
    if (!$stats && auth()->check()) {
        $stats = app(\App\Services\Tutorial\TutorialProgressService::class)->getUserStats(auth()->user());
    }
@endphp

@if($stats)
<div class="space-y-8">
    <!-- Main Stats Section with Beautiful Gradient -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-blue-600 dark:from-indigo-900 dark:to-blue-900 p-8 shadow-lg">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#grid)" />
            </svg>
        </div>

        <!-- Content -->
        <div class="relative z-10">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-1">📚 Learning Journey</h2>
                    <p class="text-indigo-100">You're on fire! Keep up the momentum</p>
                </div>
                @if($stats['total_points'] > 0)
                <div class="text-center bg-yellow-400 dark:bg-yellow-500 text-yellow-900 rounded-xl px-4 py-3 shadow-lg">
                    <div class="text-2xl font-bold">⭐ {{ $stats['total_points'] }}</div>
                    <div class="text-xs font-semibold">Points</div>
                </div>
                @endif
            </div>

            <!-- Progress Grid - 3 Columns -->
            <div class="grid grid-cols-3 gap-4">
                <!-- Completed Parts -->
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                    <div class="text-4xl font-bold text-white">{{ $stats['total_parts_completed'] }}</div>
                    <p class="text-sm text-white/90 mt-1">Parts Completed</p>
                    @if($stats['total_parts_read'] > 0)
                    <div class="mt-3 w-full bg-white/20 rounded-full h-2">
                        <div class="bg-green-400 h-2 rounded-full transition-all duration-500"
                             style="width: {{ ($stats['total_parts_completed'] / $stats['total_parts_read']) * 100 }}%"></div>
                    </div>
                    <p class="text-xs text-white/80 mt-1 font-medium">{{ number_format(($stats['total_parts_completed'] / $stats['total_parts_read']) * 100, 0) }}% done</p>
                    @endif
                </div>

                <!-- Series Completed -->
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                    <div class="text-4xl font-bold text-white">{{ $stats['total_series_completed'] }}</div>
                    <p class="text-sm text-white/90 mt-1">Series Finished</p>
                    <div class="mt-3 text-xs text-white/80 font-medium">🏆 Achievement Unlocked</div>
                </div>

                <!-- Hours Spent -->
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                    <div class="text-4xl font-bold text-white">{{ $stats['total_hours_spent'] }}</div>
                    <p class="text-sm text-white/90 mt-1">Hours Learning</p>
                    <div class="mt-3 text-xs text-white/80 font-medium">⏱️ Keep it up!</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Achievements Grid - Like Featured Posts -->
    @if($stats['total_achievements'] > 0)
    <div>
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>🏅</span>
                Achievements Earned
                <span class="inline-flex items-center justify-center w-6 h-6 ml-2 text-sm font-bold text-white bg-blue-600 rounded-full">
                    {{ $stats['total_achievements'] }}
                </span>
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Celebrate your learning milestones</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse(auth()->user()->achievements()->latest('achieved_at')->take(6)->get() as $achievement)
            <a href="javascript:void(0)" class="group block">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 h-48 cursor-pointer shadow-md hover:shadow-xl transition-all duration-300 hover:scale-105">
                    <!-- Achievement Icon/Image Background -->
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500 via-blue-500 to-pink-500 opacity-20 group-hover:opacity-30 transition-opacity"></div>

                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                    <!-- Content -->
                    <div class="absolute bottom-0 left-0 right-0 p-5">
                        <!-- Achievement Badge -->
                        <div class="mb-3">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-yellow-400 text-2xl shadow-lg">
                                🏆
                            </span>
                        </div>

                        <!-- Title and Description -->
                        <h4 class="text-white font-bold text-lg line-clamp-2 mb-2">
                            {{ $achievement->name ?? 'Achievement' }}
                        </h4>
                        <p class="text-white/80 text-sm line-clamp-2 mb-3">
                            {{ $achievement->description ?? 'Great progress!' }}
                        </p>

                        <!-- Meta Info -->
                        <div class="flex items-center justify-between text-xs text-white/70">
                            <span class="font-medium">Earned {{ $achievement->pivot->achieved_at?->format('M j, Y') ?? 'Recently' }}</span>
                            <span class="flex items-center gap-1">
                                ⭐ {{ $achievement->points ?? 10 }} XP
                            </span>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-12 px-6 bg-gray-50 dark:bg-slate-900/50 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                <div class="text-5xl mb-3">🎯</div>
                <p class="text-gray-900 dark:text-white font-semibold mb-1">No achievements yet</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Start learning to unlock your first achievement!</p>
            </div>
            @endforelse
        </div>
    </div>
    @endif

    <!-- Call to Action Section -->
    @if($stats['total_parts_completed'] === 0)
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 dark:from-blue-900 dark:to-cyan-900 p-8 shadow-lg">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-bold text-white mb-2">🚀 Ready to Start Learning?</h3>
                <p class="text-blue-100">Explore hundreds of tutorials and start your learning journey</p>
            </div>
            <a href="{{ route('tutorials.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-blue-600 font-semibold rounded-xl hover:bg-blue-50 hover:scale-105 transition-all shadow-lg">
                Start Learning
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
    @elseif($stats['total_parts_completed'] > 0 && $stats['total_achievements'] < 3)
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-600 to-pink-600 dark:from-purple-900 dark:to-pink-900 p-8 shadow-lg">
        <div class="absolute top-0 left-0 -mt-8 -ml-8 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative z-10">
            <h3 class="text-2xl font-bold text-white mb-2">💪 Keep Going! More Achievements Await</h3>
            <p class="text-purple-100 mb-4">Complete more tutorials and series to unlock exclusive badges and boost your learning streak</p>
            <div class="flex items-center gap-3 text-white text-sm font-medium">
                <span class="inline-flex items-center gap-1 bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">
                    📚 {{ 3 - $stats['total_achievements'] }} badges to go
                </span>
                <span class="inline-flex items-center gap-1 bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">
                    🔥 {{ $stats['total_hours_spent'] === 0 ? 'Start now' : 'You\'re on a roll!' }}
                </span>
            </div>
        </div>
    </div>
    @endif
</div>
@endif
