@extends('layouts.app')

@section('title', 'Analytics - Dashboard')
@section('description', 'Track your post performance, views, engagement, and traffic metrics')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-green-500/20 text-green-200">📊 Analytics</span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Content Analytics</h1>
        <p class="mt-4 text-base text-slate-300">Track views, engagement, and performance metrics across your content.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl space-y-12">
        <!-- Key Metrics -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Views</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalViews) }}</p>
                    </div>
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Likes</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalLikes) }}</p>
                    </div>
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Comments</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalComments) }}</p>
                    </div>
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2h-4l-4 4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Shares</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalShares) }}</p>
                    </div>
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.839 10.053 13.081 7 17 7c3.996 0 7.237 3.055 8.368 6.342M8 17c0 .933-.09 1.843-.266 2.724"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Engagement Rate</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($engagementRate, 2) }}%</p>
                    </div>
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Engagement Metrics Comparison -->
            <x-chart-bar
                title="Engagement Metrics"
                :labels="['Likes', 'Comments', 'Shares']"
                :data="[$totalLikes, $totalComments, $totalShares]"
                color="blue"
            />

            <!-- Engagement Distribution -->
            <x-chart-donut
                title="Engagement Distribution"
                :labels="['Likes', 'Comments', 'Shares']"
                :data="[$totalLikes, $totalComments, $totalShares]"
            />
        </div>

        <!-- Top Posts -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top Posts by Views</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Your highest performing content</p>
            </div>
            @if($topPosts->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Title</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Views</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Likes</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Comments</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Engagement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($topPosts as $post)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ Str::limit($post->title, 30) }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ number_format($post->views_count) }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ number_format($post->likes_count) }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ number_format($post->comments_count) }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $engagement = $post->views_count > 0
                                        ? (($post->likes_count + $post->comments_count) / $post->views_count) * 100
                                        : 0;
                                @endphp
                                <div class="flex items-center gap-2">
                                    <div class="w-12 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-blue-500 to-purple-600" style="width: {{ min($engagement * 10, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ number_format($engagement, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No posts yet</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create your first post to see analytics data</p>
                <a href="{{ route('posts.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    Create Post
                </a>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
