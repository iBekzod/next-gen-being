@extends('layouts.app')

@section('title', 'Payouts - Dashboard')
@section('description', 'Manage your payout requests and payment methods')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-indigo-500/20 text-indigo-200">💳 Payout Management</span>
            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Manage Payouts</h1>
            <p class="mt-4 text-base text-slate-300">Track payout history, request new payouts, and manage your payment methods.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="#payout-section" class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-slate-900 transition rounded-xl bg-white shadow-sm hover:-translate-y-0.5 hover:shadow-lg">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Payout Request
            </a>
        </div>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <!-- Summary Stats Cards -->
        <div class="grid grid-cols-1 gap-6 mb-12 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Requested Card -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Requested</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                ${{ number_format($totalRequested, 2) }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">All time</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-500/20">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Paid Card -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Paid</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                ${{ number_format($totalPaid, 2) }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Completed payouts</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-emerald-100 dark:bg-emerald-500/20">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Count Card -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Requests</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $pendingCount }}
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

            <!-- Average Payout Card -->
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-transparent pointer-events-none"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Average Payout</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                ${{ number_format($averagePayout, 2) }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Per completed request</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-500/20">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payout Methods & Trends Grid -->
        <div class="grid grid-cols-1 gap-12 mb-12 lg:grid-cols-2">
            <!-- Payout Methods -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Payout Methods
                    </h2>
                </div>
                <div class="p-6">
                    @if($payoutMethods->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($payoutMethods as $method)
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            @switch($method->payout_method)
                                                @case('stripe')
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.438 1.844-1.438.777 0 1.527.386 2.069.793.42.299.940.778 1.272 1.447a6.992 6.992 0 01.56-.649 4.784 4.784 0 00-2.1-3.45c-.963-.433-2.109-.667-3.274-.667-2.965 0-5.461 1.26-5.461 3.159 0 2.086 1.694 2.773 5.148 3.915.675.243 1.156.427 1.42.667.25.207.386.523.386.923 0 .995-.573 1.948-2.342 1.948-1.143 0-2.074-.513-2.555-1.379-.18-.362-.434-.82-.821-1.48a24.488 24.488 0 01-1.081 1.637c.527.93 1.989 2.511 4.05 3.05 1.049.344 2.205.534 3.501.534 3.078 0 5.365-1.812 5.365-3.289 0-1.995-1.486-2.884-5.268-4.042z"/>
                                                    </svg>
                                                    Stripe
                                                @break
                                                @case('paypal')
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M20.067 8.478c.492.88.556 2.014.3 3.327-.74 3.806-3.276 5.12-6.514 5.12h-.5a.805.805 0 00-.794.707l-.04.22-.63 3.993-.027.15a.806.806 0 01-.795.707h-2.89a.577.577 0 01-.57-.645l1.498-9.502h.003c.04-.23.24-.398.474-.398h1.147c2.276 0 4.055-.562 4.814-2.19.42-.88.498-2.14.274-3.434-.05-.3-.122-.585-.216-.85a5.97 5.97 0 00-1.077-1.78 3.315 3.315 0 00-1.206-.754 4.513 4.513 0 00-1.618-.278H8.33a.806.806 0 00-.795.707l-.628 3.986-.028.15a.806.806 0 01-.795.707H4.194a.577.577 0 01-.57-.645l1.498-9.502h.003c.04-.23.24-.398.474-.398h2.89c3.15 0 5.305.616 6.577 2.19z"/>
                                                    </svg>
                                                    PayPal
                                                @break
                                                @case('bank_transfer')
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                    Bank Transfer
                                                @break
                                                @default
                                                    {{ Str::title(str_replace('_', ' ', $method->payout_method)) }}
                                            @endswitch
                                        </span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">${{ number_format($method->total, 2) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-2">
                                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all"
                                             style="width: {{ ($method->total / $payoutMethods->sum('total')) * 100 }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $method->count }} payouts</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8">
                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">No payouts yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 30-Day Payout Trend -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        30-Day Payout Trend
                    </h2>
                </div>
                <div class="p-6">
                    @if($monthlyPayouts->isNotEmpty())
                        <div class="flex items-end justify-between gap-1 h-64">
                            @php
                                $maxPayout = $monthlyPayouts->max('total') ?? 1;
                                $allDates = collect();
                                for ($i = 29; $i >= 0; $i--) {
                                    $allDates->push(now()->subDays($i)->format('Y-m-d'));
                                }
                            @endphp
                            @foreach($allDates as $date)
                                @php
                                    $payout = $monthlyPayouts->firstWhere('date', $date)?->total ?? 0;
                                    $height = $maxPayout > 0 ? ($payout / $maxPayout) * 100 : 0;
                                @endphp
                                <div class="flex flex-col items-center flex-1 gap-2 group">
                                    <div class="w-full bg-gradient-to-t from-indigo-500 to-indigo-400 rounded-t transition-all hover:from-indigo-600 hover:to-indigo-500 hover:shadow-lg"
                                         style="height: {{ max($height, 4) }}%"
                                         title="${{ number_format($payout, 2) }}">
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 rotate-45 origin-left transform group-hover:text-gray-700 dark:group-hover:text-gray-200 transition">
                                        {{ \Carbon\Carbon::parse($date)->format('M d') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12">
                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">No payouts in the last 30 days</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payout Requests Table -->
        <div id="payout-section" class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        Payout Request History
                    </h2>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $payoutRequests->total() }} total
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                @if($payoutRequests->isNotEmpty())
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Amount</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Method</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Requested</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Processed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($payoutRequests as $request)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                ${{ number_format($request->amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
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
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                @if($request->processed_at)
                                    {{ $request->processed_at->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                    {{ $payoutRequests->links('pagination::tailwind') }}
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">No payout requests yet</p>
                    <a href="#" class="inline-flex items-center gap-2 px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Your First Payout Request
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Tax & Invoices Section -->
        <div class="mt-12 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-800 border border-blue-200 dark:border-slate-700 p-8">
            <div class="flex gap-6 items-start">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tax & Invoices</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">Keep track of your earnings for tax purposes and download invoices for your records.</p>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a href="#" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download Tax Summary
                        </a>
                        <a href="#" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-700 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Invoice Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
