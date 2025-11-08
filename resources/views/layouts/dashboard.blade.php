@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-gray-50 dark:bg-slate-950">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-slate-800 shadow-sm overflow-y-auto">
        <!-- Dashboard Header -->
        <div class="sticky top-0 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Welcome back, {{ Auth::user()->name }}</p>
        </div>

        <!-- Navigation Menu -->
        <nav class="p-6 space-y-2">
            <!-- Main Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                @if(Route::current()->getName() === 'dashboard')
                    bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                @else
                    text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                @endif
            ">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m0 0l4 4m-4-4v4"/>
                </svg>
                Overview
            </a>

            <!-- Content Management -->
            <div class="mt-6">
                <p class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Content</p>
                <a href="{{ route('dashboard.posts') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.posts'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    My Posts
                </a>
                <a href="{{ route('dashboard.calendar') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.calendar'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Calendar
                </a>
                <a href="{{ route('posts.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Write New Post
                </a>
            </div>

            <!-- Analytics & Performance -->
            <div class="mt-6">
                <p class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Analytics</p>
                <a href="{{ route('dashboard.analytics') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.analytics'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Analytics
                </a>
                <a href="{{ route('dashboard.videos') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.videos'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Videos
                </a>
            </div>

            <!-- Monetization -->
            <div class="mt-6">
                <p class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Monetization</p>
                <a href="{{ route('dashboard.earnings') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.earnings'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Earnings
                </a>
                <a href="{{ route('dashboard.payouts') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.payouts'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Payouts
                </a>
                <a href="{{ route('dashboard.quota') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.quota'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    AI Quota
                </a>
            </div>

            <!-- Distribution -->
            <div class="mt-6">
                <p class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Distribution</p>
                <a href="{{ route('dashboard.social-media') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.social-media'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Social Media
                </a>
                <a href="{{ route('dashboard.webhooks') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.webhooks'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Webhooks
                </a>
            </div>

            <!-- System -->
            <div class="mt-6">
                <p class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">System</p>
                <a href="{{ route('dashboard.notifications') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition relative
                    @if(str_contains(Route::current()->getName(), 'dashboard.notifications'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifications
                    @if(\App\Models\Notification::where('user_id', Auth::id())->whereNull('read_at')->count() > 0)
                    <span class="absolute top-2 right-2 inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                    @endif
                </a>
                <a href="{{ route('dashboard.jobs') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.jobs'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Jobs
                </a>
                <a href="{{ route('dashboard.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition
                    @if(str_contains(Route::current()->getName(), 'dashboard.settings'))
                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                    @else
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800
                    @endif
                ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
            </div>
        </nav>

        <!-- Help Section -->
        <div class="p-6 border-t border-gray-200 dark:border-slate-800">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-300">Need help?</p>
                <p class="text-xs text-blue-800 dark:text-blue-400 mt-1">Check our documentation or contact support.</p>
                <a href="#" class="mt-2 inline-block text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                    Visit Help Center →
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-auto">
        @yield('dashboard_content')
    </main>
</div>
@endsection
