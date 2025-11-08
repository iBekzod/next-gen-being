@extends('layouts.app')

@section('title', 'Video Management - Dashboard')
@section('description', 'Manage your video generations and processing queue')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">🎬 Video Studio</span>
            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Video Management</h1>
            <p class="mt-4 text-base text-slate-300">Track your video generations, monitor processing status, and manage your queue.</p>
        </div>
        <a href="javascript:alert('🎬 Start generating a new video in your post editor!')" class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-slate-900 transition rounded-xl bg-white shadow-sm hover:-translate-y-0.5 hover:shadow-lg">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Generate New Video
        </a>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <!-- Summary Stats -->
        <div class="grid grid-cols-1 gap-6 mb-12 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Total Videos -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Videos</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalVideos }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">All time</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-500/20">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Completed</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $completedVideos }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $totalVideos > 0 ? round(($completedVideos / $totalVideos) * 100) : 0 }}% success rate</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-emerald-100 dark:bg-emerald-500/20">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Processing -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Processing</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $processingCount }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">In progress</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-amber-100 dark:bg-amber-500/20">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Failed -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Failed</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $failedCount }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Retry available</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-red-100 dark:bg-red-500/20">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Queued -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Queued</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $queuedCount }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Waiting</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-500/20">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost & Credits Section -->
        <div class="grid grid-cols-1 gap-6 mb-12 lg:grid-cols-2">
            <!-- AI Credits Used -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-slate-700 dark:to-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        AI Credits Used
                    </h2>
                </div>
                <div class="p-6">
                    <div class="flex items-baseline justify-between mb-4">
                        <div>
                            <p class="text-5xl font-bold text-gray-900 dark:text-white">{{ $totalCreditsUsed }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Total AI credits consumed</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg per video</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $totalVideos > 0 ? round($totalCreditsUsed / $totalVideos) : 0 }}</p>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 pt-4 border-t border-gray-200 dark:border-slate-700">
                        📊 Track your AI subscription usage in settings
                    </div>
                </div>
            </div>

            <!-- Generation Costs -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Total Cost
                    </h2>
                </div>
                <div class="p-6">
                    <div class="flex items-baseline justify-between mb-4">
                        <div>
                            <p class="text-5xl font-bold text-gray-900 dark:text-white">${{ number_format($totalGenerationCost, 2) }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">All video generation costs</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg per video</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">${{ $totalVideos > 0 ? number_format($totalGenerationCost / $totalVideos, 2) : '0.00' }}</p>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 pt-4 border-t border-gray-200 dark:border-slate-700">
                        💰 Optimize quality settings to reduce costs
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Types Breakdown -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden mb-12">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Video Types Breakdown
                </h2>
            </div>
            <div class="p-6">
                @if($videosByType->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($videosByType as $video)
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        @switch($video->video_type)
                                            @case('youtube')
                                                📺 YouTube Video
                                            @break
                                            @case('tiktok')
                                                🎵 TikTok
                                            @break
                                            @case('reel')
                                                ✨ Instagram Reel
                                            @break
                                            @case('short')
                                                ⚡ YouTube Short
                                            @break
                                            @default
                                                {{ Str::title(str_replace('_', ' ', $video->video_type)) }}
                                        @endswitch
                                    </span>
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $video->count }} videos</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($video->total_size, 2) }} MB</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 h-2 rounded-full transition-all"
                                         style="width: {{ ($video->count / $videosByType->sum('count')) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <p class="text-gray-600 dark:text-gray-400">No videos generated yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Videos Table -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Recent Videos
                    </h2>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $recentVideos->total() }} total
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                @if($recentVideos->isNotEmpty())
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Type</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Post</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Duration</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">File Size</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($recentVideos as $video)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      @switch($video->video_type)
                                          @case('youtube')
                                              class="bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300"
                                          @break
                                          @case('tiktok')
                                              class="bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-300"
                                          @break
                                          @case('reel')
                                              class="bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300"
                                          @break
                                          @case('short')
                                              class="bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300"
                                          @break
                                          @default
                                              class="bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300"
                                      @endswitch>
                                    {{ $video->getVideoTypeName() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                {{ $video->post?->title ?? 'Deleted Post' }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ $video->getFormattedDuration() }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ $video->file_size_mb ? number_format($video->file_size_mb, 2) . ' MB' : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @switch($video->status)
                                    @case('completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300">
                                            <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                            Ready
                                        </span>
                                    @break
                                    @case('processing')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300">
                                            <svg class="w-3 h-3 mr-1 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Processing
                                        </span>
                                    @break
                                    @case('queued')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300">
                                            <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Queued
                                        </span>
                                    @break
                                    @case('failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300">
                                            <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Failed
                                        </span>
                                    @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ $video->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                    {{ $recentVideos->links('pagination::tailwind') }}
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">No videos generated yet</p>
                    <a href="javascript:alert('🎬 Start generating a new video in your post editor!')" class="inline-flex items-center gap-2 px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Generate Your First Video
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-12 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-800 border border-blue-200 dark:border-slate-700 p-8">
            <div class="flex gap-6 items-start">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Video Generation Tips</h3>
                    <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                        <li>✓ Generate multiple video formats from one post (TikTok, Reels, Shorts)</li>
                        <li>✓ Schedule videos for automatic publishing when ready</li>
                        <li>✓ Monitor processing status in real-time</li>
                        <li>✓ Retry failed videos up to 3 times automatically</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
