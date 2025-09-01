<div class="mt-12">
    <!-- Pricing Grid -->
    <div class="grid gap-8 lg:grid-cols-3">
        @foreach ($plans as $key => $plan)
            <div
                class="relative flex flex-col rounded-lg shadow-lg overflow-hidden {{ $key === 'pro' ? 'border-2 border-blue-500' : 'border border-gray-200 dark:border-gray-700' }}">
                @if ($key === 'pro')
                    <div
                        class="absolute top-0 right-0 px-3 py-1 text-xs font-medium text-white bg-blue-500 rounded-bl-lg">
                        Most Popular
                    </div>
                @endif

                <div class="px-6 py-8 bg-white dark:bg-gray-800">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $plan['name'] }}</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        @if ($key === 'basic')
                            Perfect for casual readers
                        @elseif($key === 'pro')
                            Best for professionals
                        @else
                            For teams and organizations
                        @endif
                    </p>
                    <div class="mt-4">
                        <span
                            class="text-4xl font-bold text-gray-900 dark:text-white">${{ number_format($plan['price'], 0) }}</span>
                        <span class="text-gray-500 dark:text-gray-400">/ {{ $plan['interval'] }}</span>
                    </div>
                </div>

                <div class="flex-1 px-6 py-8 bg-gray-50 dark:bg-gray-900">
                    <ul class="space-y-4">
                        @foreach ($plan['features'] as $feature)
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 w-5 h-5 text-green-500 mt-0.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="ml-3 text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        @auth
                            @php
                                $subscription = $currentSubscription;
                                $isCurrentPlan =
                                    $subscription &&
                                    $subscription->isActive() &&
                                    $subscription->variant_id == $plan['variant_id'];
                            @endphp

                            @if ($isCurrentPlan)
                                <button disabled
                                    class="w-full px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md cursor-not-allowed dark:bg-gray-700 dark:text-gray-400">
                                    Current Plan
                                </button>
                            @else
                                <button wire:click="subscribe('{{ $key }}')" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    class="w-full px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 rounded-md hover:bg-blue-700">
                                    <span wire:loading.remove wire:target="subscribe('{{ $key }}')">
                                        {{ $subscription ? 'Switch to ' . $plan['name'] : 'Start ' . $plan['interval'] . 'ly subscription' }}
                                    </span>
                                    <span wire:loading wire:target="subscribe('{{ $key }}')"
                                        class="flex items-center justify-center">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </span>
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}?redirect={{ urlencode(route('subscription.plans')) }}"
                                class="block w-full px-4 py-2 text-sm font-medium text-center text-white transition-colors bg-blue-600 rounded-md hover:bg-blue-700">
                                Sign in to subscribe
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Features Comparison -->
    <div class="mt-16">
        <h2 class="mb-8 text-2xl font-bold text-center text-gray-900 dark:text-white">
            Detailed Feature Comparison
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                            Features
                        </th>
                        @foreach ($plans as $plan)
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ $plan['name'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">Premium Articles</td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">Ad-free Experience</td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">Early Access</td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">Team Accounts</td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <svg class="w-5 h-5 mx-auto text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">
                            Up to 10
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="mt-16">
        <h2 class="mb-8 text-2xl font-bold text-center text-gray-900 dark:text-white">
            Frequently Asked Questions
        </h2>
        <div class="space-y-4 max-w-3xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow" x-data="{ open: false }">
                <button @click="open = !open"
                    class="w-full px-6 py-4 text-left text-gray-900 dark:text-white font-medium flex items-center justify-between">
                    <span>Can I cancel my subscription anytime?</span>
                    <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>
                <div x-show="open" x-transition class="px-6 pb-4 text-gray-600 dark:text-gray-400">
                    Yes, you can cancel your subscription at any time. You'll continue to have access to premium
                    features until the end of your current billing period.
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow" x-data="{ open: false }">
                <button @click="open = !open"
                    class="w-full px-6 py-4 text-left text-gray-900 dark:text-white font-medium flex items-center justify-between">
                    <span>What payment methods do you accept?</span>
                    <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>
                <div x-show="open" x-transition class="px-6 pb-4 text-gray-600 dark:text-gray-400">
                    We accept all major credit cards (Visa, Mastercard, American Express) and debit cards. All payments
                    are processed securely through LemonSqueezy.
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow" x-data="{ open: false }">
                <button @click="open = !open"
                    class="w-full px-6 py-4 text-left text-gray-900 dark:text-white font-medium flex items-center justify-between">
                    <span>Is there a free trial?</span>
                    <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>
                <div x-show="open" x-transition class="px-6 pb-4 text-gray-600 dark:text-gray-400">
                    We offer a 7-day free trial for new subscribers. You can cancel anytime during the trial period
                    without being charged.
                </div>
            </div>
        </div>
    </div>
</div>
