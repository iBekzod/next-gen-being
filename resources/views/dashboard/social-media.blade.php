@extends('layouts.app')

@section('title', 'Social Media Manager - Dashboard')
@section('description', 'Manage your social media accounts and publishing settings')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-purple-500/20 text-purple-200">📱 Social Distribution</span>
            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Social Media Manager</h1>
            <p class="mt-4 text-base text-slate-300">Connect accounts, manage auto-publishing, and track your social presence.</p>
        </div>
        <a href="{{ route('social.auth.redirect', 'youtube') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-slate-900 transition rounded-xl bg-white shadow-sm hover:-translate-y-0.5 hover:shadow-lg">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Connect Platform
        </a>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 mb-12 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Active Accounts -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Accounts</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $activeAccounts }}/{{ $totalAccounts }}</p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Connected platforms</p>
                </div>
            </div>

            <!-- Total Followers -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Followers</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalFollowers) }}</p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Across all platforms</p>
                </div>
            </div>

            <!-- Auto-Publish Enabled -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Auto-Publish Active</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $autoPublishEnabled }}</p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Accounts enabled</p>
                </div>
            </div>

            <!-- Token Issues -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Expired Tokens</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $expiredTokens }}</p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Need reconnection</p>
                </div>
            </div>
        </div>

        <!-- Platform Breakdown -->
        @if($accountsByPlatform->isNotEmpty())
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden mb-12">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Platform Distribution
                </h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($accountsByPlatform as $platform)
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    @switch($platform->platform)
                                        @case('youtube')
                                            📺 YouTube
                                        @break
                                        @case('instagram')
                                            📸 Instagram
                                        @break
                                        @case('twitter')
                                            𝕏 Twitter/X
                                        @break
                                        @case('linkedin')
                                            💼 LinkedIn
                                        @break
                                        @case('tiktok')
                                            🎵 TikTok
                                        @break
                                        @default
                                            {{ Str::title($platform->platform) }}
                                    @endswitch
                                </span>
                                <div class="text-right">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $platform->count }} account{{ $platform->count !== 1 ? 's' : '' }}</span>
                                    @if($platform->total_followers)
                                    <span class="text-sm text-gray-600 dark:text-gray-400 ml-4">{{ number_format($platform->total_followers) }} followers</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Connected Accounts Table -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2z"/></svg>
                    Your Accounts
                </h2>
            </div>
            <div class="overflow-x-auto">
                @if($socialMediaAccounts->isNotEmpty())
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Platform</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Account</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Followers</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Token Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Auto-Publish</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Connected</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($socialMediaAccounts as $account)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      @switch($account->platform)
                                          @case('youtube')
                                              class="bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300"
                                          @break
                                          @case('instagram')
                                              class="bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300"
                                          @break
                                          @case('twitter')
                                              class="bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300"
                                          @break
                                          @case('linkedin')
                                              class="bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300"
                                          @break
                                          @case('tiktok')
                                              class="bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-300"
                                          @break
                                      @endswitch>
                                    {{ $account->getPlatformDisplayName() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">
                                {{ $account->platform_username }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ $account->follower_count ? number_format($account->follower_count) : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($account->isTokenExpired())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300">
                                        <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Expired
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300">
                                        <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                        Valid
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($account->auto_publish)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300">
                                        ✓ Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-300">
                                        — Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ $account->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                    {{ $socialMediaAccounts->links('pagination::tailwind') }}
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2z"/></svg>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">No social accounts connected yet</p>
                    <a href="{{ route('social.auth.redirect', 'youtube') }}" class="inline-flex items-center gap-2 px-6 py-2 text-sm font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Connect Your First Account
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Tips Section -->
        <div class="mt-12 rounded-xl bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-slate-800 dark:to-slate-800 border border-purple-200 dark:border-slate-700 p-8">
            <div class="flex gap-6 items-start">
                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Social Media Best Practices</h3>
                    <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                        <li>✓ Connect verified accounts for better engagement</li>
                        <li>✓ Enable auto-publish for scheduled posting</li>
                        <li>✓ Reconnect accounts if tokens expire</li>
                        <li>✓ Monitor follower counts for growth metrics</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
