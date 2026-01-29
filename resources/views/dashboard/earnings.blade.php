@extends('layouts.app')

@section('title', 'Earnings - Dashboard')
@section('description', 'Track your earnings, payouts, and revenue analytics')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-emerald-500/20 text-emerald-200">💰 Monetization</span>
            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Earnings & Payouts</h1>
            <p class="mt-4 text-base text-slate-300">Track your revenue from premium content, engagement bonuses, and milestone rewards.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('subscription.manage') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-slate-900 transition rounded-xl bg-white shadow-sm hover:-translate-y-0.5 hover:shadow-lg">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Request Payout
            </a>
        </div>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <!-- Summary Stats Cards -->
        <div class="grid grid-cols-1 gap-6 mb-12 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Total Earnings Card -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Earnings</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                ${{ number_format($totalEarnings, 2) }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Lifetime earnings</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-emerald-100 dark:bg-emerald-500/20">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- This Month Card -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Month</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                ${{ number_format($thisMonthEarnings, 2) }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ now()->format('F Y') }} earnings (all)</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-500/20">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paid Earnings Card -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Paid Earnings</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                ${{ number_format($paidEarnings, 2) }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Successfully paid out</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-green-100 dark:bg-green-500/20">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Earnings Card -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Earnings</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                ${{ number_format($pendingPayouts, 2) }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Awaiting processing</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-amber-100 dark:bg-amber-500/20">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payout Requests Card -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Payout Requests</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $payoutRequests->count() }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $payoutRequests->where('status', 'pending')->count() }} pending
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-500/20">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 30-Day Earnings Trend Chart -->
        @php
            $allDates = collect();
            for ($i = 29; $i >= 0; $i--) {
                $allDates->push(now()->subDays($i)->format('Y-m-d'));
            }
            $earningsData = [];
            $earningsLabels = [];
            foreach ($allDates as $date) {
                $earning = $dailyEarnings->firstWhere('date', $date)?->total ?? 0;
                $earningsData[] = (int)$earning;
                $earningsLabels[] = \Carbon\Carbon::parse($date)->format('M d');
            }
        @endphp
        @if($dailyEarnings->isNotEmpty())
        <x-chart-line
            title="30-Day Earnings Trend"
            :labels="$earningsLabels"
            :data="$earningsData"
            color="green"
        />
        @else
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">30-Day Earnings Trend</h2>
            </div>
            <div class="p-12 text-center">
                <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-gray-600 dark:text-gray-400">No earnings in the last 30 days</p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">Start creating premium content to generate earnings</p>
            </div>
        </div>
        @endif

        <!-- Earnings by Source & Top Posts Grid -->
        <div class="grid grid-cols-1 gap-12 mb-12 lg:grid-cols-2">
            <!-- Earnings by Source Distribution Chart -->
            @if($earningsByType->isNotEmpty())
            @php
                $sourceLabels = [];
                $sourceData = [];
                foreach($earningsByType as $earning) {
                    $sourceLabels[] = match($earning->type) {
                        'premium_content' => 'Premium Content',
                        'engagement_bonus' => 'Engagement Bonus',
                        'follower_milestone' => 'Follower Milestone',
                        'referral' => 'Referral',
                        default => Str::title(str_replace('_', ' ', $earning->type)),
                    };
                    $sourceData[] = (int)$earning->total;
                }
            @endphp
            <x-chart-donut
                title="Earnings by Source"
                :labels="$sourceLabels"
                :data="$sourceData"
            />
            @else
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Earnings by Source</h2>
                </div>
                <div class="p-12 text-center">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400">No earnings yet</p>
                </div>
            </div>
            @endif

            <!-- Top Earning Posts -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Top Earning Posts
                    </h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-slate-700">
                    @if($topPosts->isNotEmpty())
                        @foreach($topPosts as $earning)
                            <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                <div class="flex items-start justify-between gap-4 mb-2">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ $earning->metadata['post_title'] ?? 'Untitled Post' }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $earning->paid_at?->format('M d, Y') ?? 'Not paid yet' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-emerald-600 dark:text-emerald-400">
                                            ${{ number_format($earning->amount, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="px-6 py-8 text-center">
                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-3 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">No premium earnings yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payout Requests Section -->
        @if($payoutRequests->isNotEmpty())
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        Recent Payout Requests
                    </h2>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $payoutRequests->count() }} total
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Amount</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Method</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($payoutRequests as $request)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                ${{ number_format($request->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      @switch($request->payout_method)
                                          @case('stripe')
                                              style="background-color: rgb(6, 28, 63); color: rgb(96, 165, 250);"
                                          @break
                                          @case('paypal')
                                              style="background-color: rgb(25, 56, 109); color: rgb(59, 130, 246);"
                                          @break
                                          @case('bank_transfer')
                                              style="background-color: rgb(30, 41, 59); color: rgb(148, 163, 184);"
                                          @break
                                          @default
                                              class="bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300"
                                      @endswitch>
                                    {{ Str::title(str_replace('_', ' ', $request->payout_method)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @switch($request->status)
                                    @case('pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300">
                                            <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Pending
                                        </span>
                                    @break
                                    @case('completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300">
                                            <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                            Completed
                                        </span>
                                    @break
                                    @case('rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300">
                                            <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Rejected
                                        </span>
                                    @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ $request->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="text-center py-12 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700">
            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <p class="text-gray-600 dark:text-gray-400 mb-6">No payout requests yet</p>
            <a href="{{ route('subscription.manage') }}" class="inline-flex items-center gap-2 px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Request Your First Payout
            </a>
        </div>
        @endif

        <!-- Help Section -->
        <div class="mt-12 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-800 border border-blue-200 dark:border-slate-700 p-8">
            <div class="flex gap-6 items-start">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">How to Maximize Your Earnings</h3>
                    <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                        <li>✓ Create premium content that engages your audience</li>
                        <li>✓ Build your subscriber base to unlock milestone bonuses</li>
                        <li>✓ Enable engagement tracking on your posts</li>
                        <li>✓ Request payouts when you've accumulated earnings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
