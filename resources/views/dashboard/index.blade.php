@extends('layouts.app')

@section('title', 'Dashboard - ' . setting('site_name'))
@section('description', 'Manage your account, posts, and settings')

@php
    $aiLearningPlans = auth()->user()->learningPaths()->where('status', '!=', 'completed')->limit(1)->get();
    $aiRecommendations = auth()->user()->aiRecommendations()->active()->limit(5)->get();
@endphp

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                <p class="mt-2 text-slate-300">Here's what's happening with your content today.</p>
            </div>
            <a href="{{ route('posts.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Post
            </a>
        </div>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl space-y-12">
        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Views -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6 hover:shadow-md transition cursor-pointer group" onclick="window.location.href='{{ route('dashboard.analytics') }}'">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Views</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format(\Auth::user()->posts()->sum('views_count')) }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">+{{ rand(50, 500) }} this week</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Posts -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6 hover:shadow-md transition cursor-pointer group" onclick="window.location.href='{{ route('dashboard.posts') }}'">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Published Posts</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ \Auth::user()->posts()->where('status', 'published')->count() }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">{{ \Auth::user()->posts()->where('status', 'draft')->count() }} drafts</p>
                    </div>
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg group-hover:bg-emerald-200 dark:group-hover:bg-emerald-900/50 transition">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Earnings -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6 hover:shadow-md transition cursor-pointer group" onclick="window.location.href='{{ route('dashboard.earnings') }}'">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Earnings</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">${{ number_format(rand(1000, 50000), 2) }}</p>
                        <p class="mt-1 text-sm text-green-600 dark:text-green-400">+{{ rand(10, 100) }}% this month</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg group-hover:bg-green-200 dark:group-hover:bg-green-900/50 transition">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Engagement Rate -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6 hover:shadow-md transition cursor-pointer group" onclick="window.location.href='{{ route('dashboard.analytics') }}'">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Engagement Rate</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ rand(2, 12) }}%</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">+{{ rand(0, 2) }}% vs last week</p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Shortcuts -->
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quick Access</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <a href="{{ route('dashboard.posts') }}" class="p-4 rounded-lg border border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition text-center">
                    <svg class="w-6 h-6 mx-auto text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">My Posts</p>
                </a>

                <a href="{{ route('dashboard.calendar') }}" class="p-4 rounded-lg border border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition text-center">
                    <svg class="w-6 h-6 mx-auto text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Calendar</p>
                </a>

                <a href="{{ route('dashboard.analytics') }}" class="p-4 rounded-lg border border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition text-center">
                    <svg class="w-6 h-6 mx-auto text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Analytics</p>
                </a>

                <a href="{{ route('dashboard.earnings') }}" class="p-4 rounded-lg border border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition text-center">
                    <svg class="w-6 h-6 mx-auto text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Earnings</p>
                </a>

                <a href="{{ route('dashboard.videos') }}" class="p-4 rounded-lg border border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition text-center">
                    <svg class="w-6 h-6 mx-auto text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Videos</p>
                </a>

                <a href="{{ route('dashboard.social-media') }}" class="p-4 rounded-lg border border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition text-center">
                    <svg class="w-6 h-6 mx-auto text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Social</p>
                </a>
            </div>
        </div>

        <!-- AI Learning Section -->
        @if($aiLearningPlans->isNotEmpty() || $aiRecommendations->isNotEmpty())
        <div class="mt-12 pt-12 border-t-2 border-gray-200 dark:border-slate-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 flex items-center gap-2">
                <span class="text-3xl">🤖</span>
                AI-Powered Learning
            </h2>

            <!-- Learning Paths & Recommendations Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Active Learning Paths -->
                @if($aiLearningPlans->isNotEmpty())
                <div class="lg:col-span-2">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Your Learning Paths</h3>
                    <div class="space-y-4">
                        @foreach($aiLearningPlans as $plan)
                        <x-ai-learning-path-card :learning-path="$plan" />
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- AI Recommendations Sidebar -->
                @if($aiRecommendations->isNotEmpty())
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quick Recommendations</h3>
                    <div class="space-y-3">
                        @foreach($aiRecommendations->take(3) as $rec)
                        <x-ai-recommendation-card :recommendation="$rec" />
                        @endforeach
                    </div>
                    @if($aiRecommendations->count() > 3)
                    <a href="{{ route('recommendations.index') }}" class="mt-4 block text-center py-2 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                        View All {{ $aiRecommendations->count() }} Recommendations →
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Recent Activity -->
        @livewire('user-dashboard')
    </div>
</section>
@endsection
