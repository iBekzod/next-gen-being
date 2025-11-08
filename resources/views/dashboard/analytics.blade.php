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
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <!-- Metrics -->
        <div class="grid grid-cols-1 gap-6 mb-12 sm:grid-cols-2 lg:grid-cols-5">
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Views</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalViews) }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Likes</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalLikes) }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Comments</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalComments) }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Shares</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalShares) }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Engagement Rate</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($engagementRate, 2) }}%</p>
            </div>
        </div>

        <!-- Top Posts -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top Posts</h2>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($topPosts as $post)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $post->title }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ number_format($post->views_count) }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ number_format($post->likes_count) }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ number_format($post->comments_count) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">
                No posts yet
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
