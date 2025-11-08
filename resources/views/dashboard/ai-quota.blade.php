@extends('layouts.app')

@section('title', 'AI Quota & Usage - Dashboard')
@section('description', 'Monitor your AI feature usage, view quotas, and manage your subscription')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-violet-500/20 text-violet-200">⚡ AI Quota</span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">AI Usage & Quota</h1>
        <p class="mt-4 text-base text-slate-300">Monitor your AI feature usage, view quota limits, and upgrade your plan.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl space-y-12">
        <!-- Current Plan Card -->
        <div class="rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg p-8 text-white">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">Current Plan</p>
                    <h2 class="mt-2 text-3xl font-bold">{{ $currentQuota['name'] }}</h2>
                    <p class="mt-2 text-sm opacity-75">
                        {{ $currentQuota['price'] === 0 ? 'Free' : '$' . $currentQuota['price'] . '/month' }}
                    </p>
                </div>
                <div class="text-right">
                    @if($subscriptionTier === 'free')
                    <a href="{{ route('subscription.plans') }}" class="inline-flex items-center px-4 py-2 bg-white text-violet-600 font-semibold rounded-lg hover:bg-gray-100 transition">
                        Upgrade Plan
                    </a>
                    @else
                    <span class="inline-flex items-center px-4 py-2 bg-white text-violet-600 font-semibold rounded-lg">
                        ✓ Active
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Usage Overview -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            @foreach($usageBreakdown as $key => $usage)
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $usage['icon'] }} {{ str_replace('_', ' ', ucfirst($key)) }}</h3>
                    <span class="text-sm font-medium
                        @if($usage['percent'] < 50)
                            text-green-600 dark:text-green-400
                        @elseif($usage['percent'] < 80)
                            text-amber-600 dark:text-amber-400
                        @else
                            text-red-600 dark:text-red-400
                        @endif
                    ">
                        {{ number_format($usage['percent'], 0) }}%
                    </span>
                </div>

                <!-- Progress Bar -->
                <div class="space-y-2 mb-4">
                    <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full
                            @if($usage['percent'] < 50)
                                bg-green-500
                            @elseif($usage['percent'] < 80)
                                bg-amber-500
                            @else
                                bg-red-500
                            @endif
                        transition-all" style="width: {{ $usage['percent'] }}%"></div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $usage['used'] }} of {{ $usage['limit'] }} used
                    </p>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $usage['description'] }}</p>
            </div>
            @endforeach
        </div>

        <!-- Features by Plan Comparison -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Feature Comparison</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Feature</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Free</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Pro</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Business</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">Writing Assistant</td>
                            <td class="px-6 py-4 text-center">5/month</td>
                            <td class="px-6 py-4 text-center font-semibold text-blue-600 dark:text-blue-400">100/month</td>
                            <td class="px-6 py-4 text-center">500/month</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">Video Generation</td>
                            <td class="px-6 py-4 text-center text-gray-500">—</td>
                            <td class="px-6 py-4 text-center font-semibold text-blue-600 dark:text-blue-400">10/month</td>
                            <td class="px-6 py-4 text-center">100/month</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">API Calls</td>
                            <td class="px-6 py-4 text-center">100/month</td>
                            <td class="px-6 py-4 text-center font-semibold text-blue-600 dark:text-blue-400">5,000/month</td>
                            <td class="px-6 py-4 text-center">50,000/month</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">Priority Support</td>
                            <td class="px-6 py-4 text-center text-gray-500">—</td>
                            <td class="px-6 py-4 text-center font-semibold text-blue-600 dark:text-blue-400">✓</td>
                            <td class="px-6 py-4 text-center">✓</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Available Upgrades -->
        @if($subscriptionTier === 'free' || $subscriptionTier === 'pro')
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Upgrade Available</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($subscriptionTier === 'free')
                    <!-- Pro Plan -->
                    <div class="rounded-lg border-2 border-blue-500 bg-blue-50 dark:bg-blue-950/30 p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pro Plan</h3>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">$29<span class="text-lg">/mo</span></p>
                        <ul class="mt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                100 Writing Assistant checks
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                10 Video generations
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                5,000 monthly API calls
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Priority email support
                            </li>
                        </ul>
                        <a href="{{ route('subscription.plans') }}" class="mt-6 block w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-center transition">
                            Upgrade to Pro
                        </a>
                    </div>

                    <!-- Business Plan -->
                    <div class="rounded-lg border-2 border-purple-500 bg-purple-50 dark:bg-purple-950/30 p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Business Plan</h3>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-2">$99<span class="text-lg">/mo</span></p>
                        <ul class="mt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                500 Writing Assistant checks
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                100 Video generations
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                50,000 monthly API calls
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Priority 24/7 support
                            </li>
                        </ul>
                        <a href="{{ route('subscription.plans') }}" class="mt-6 block w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg text-center transition">
                            Upgrade to Business
                        </a>
                    </div>
                    @elseif($subscriptionTier === 'pro')
                    <!-- Business Plan for Pro users -->
                    <div class="rounded-lg border-2 border-purple-500 bg-purple-50 dark:bg-purple-950/30 p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Business Plan</h3>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-2">$99<span class="text-lg">/mo</span></p>
                        <ul class="mt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                500 Writing Assistant checks (5x increase)
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                100 Video generations (10x increase)
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                50,000 monthly API calls (10x increase)
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Priority 24/7 support
                            </li>
                        </ul>
                        <a href="{{ route('subscription.plans') }}" class="mt-6 block w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg text-center transition">
                            Upgrade to Business
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Usage Tips -->
        <div class="rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 p-6">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-300 mb-4">💡 Tips to Optimize Your AI Usage</h3>
            <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-300">
                <li>• Use Writing Assistant for grammar checks before publishing to improve content quality</li>
                <li>• Batch video generation requests to make the most of your monthly quota</li>
                <li>• Enable automatic social media distribution to reach more audience with fewer resources</li>
                <li>• Review your usage monthly and adjust your plan if needed</li>
                <li>• Contact support if you need a custom quota for your specific needs</li>
            </ul>
        </div>
    </div>
</section>
@endsection
