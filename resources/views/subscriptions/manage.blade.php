@extends('layouts.app')

@section('title', 'Manage Subscription - ' . setting('site_name'))
@section('description', 'Manage your subscription and billing')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-5xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">Billing</span>
        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">Manage your subscription.</h1>
        <p class="mt-4 text-base text-slate-300 max-w-3xl">Update plan status, review billing history, and keep your membership aligned with your operating rhythm.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-4xl sm:px-6 lg:px-8">
        @if($subscription)
        <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="px-4 py-5 sm:p-6">
                <!-- Current Plan -->
                <div class="mb-8">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Current Plan
                    </h2>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $subscription->name }} Plan
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Status:
                                    <span class="font-medium text-{{ $subscription->valid() ? 'green' : ($subscription->paused() ? 'yellow' : 'red') }}-600 dark:text-{{ $subscription->valid() ? 'green' : ($subscription->paused() ? 'yellow' : 'red') }}-400">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </p>
                            </div>
                            <div class="text-right">
                                @if(isset($subscription->product) && $subscription->product)
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $subscription->product->price }}
                                </p>
                                @elseif($subscription->price ?? null)
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    ${{ number_format($subscription->price / 100, 2) }}
                                </p>
                                @endif
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    per month
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Details -->
                <div class="mb-8">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Billing Details
                    </h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @if($subscription->card_brand && $subscription->card_last_four)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Method</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ ucfirst($subscription->card_brand) }} ending in {{ $subscription->card_last_four }}
                            </dd>
                        </div>
                        @endif

                        @if($subscription->created_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Member Since</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $subscription->created_at->format('F j, Y') }}
                            </dd>
                        </div>
                        @endif

                        @if($subscription->renews_at && $subscription->valid())
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Next Billing Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $subscription->renews_at->format('F j, Y') }}
                            </dd>
                        </div>
                        @endif

                        @if($subscription->ends_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $subscription->valid() ? 'Cancels On' : 'Ended On' }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $subscription->ends_at->format('F j, Y') }}
                            </dd>
                        </div>
                        @endif

                        @if($subscription->trial_ends_at && $subscription->onTrial())
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Trial Ends</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $subscription->trial_ends_at->format('F j, Y') }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Actions -->
                <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Subscription Actions
                    </h2>
                    <div class="space-y-4">
                        @if($subscription->valid() && !$subscription->ends_at)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                            <div>
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                    Cancel Subscription
                                </h3>
                                <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                    You'll continue to have access until the end of your billing period
                                </p>
                            </div>
                            <form action="{{ route('subscription.cancel.post') }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-yellow-800 transition-colors bg-yellow-100 rounded-md hover:bg-yellow-200 dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700">
                                    Cancel Subscription
                                </button>
                            </form>
                        </div>
                        @elseif($subscription->valid() && $subscription->ends_at)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-green-50 dark:bg-green-900/20">
                            <div>
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                    Resume Subscription
                                </h3>
                                <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                                    Continue your subscription after {{ $subscription->ends_at->format('F j, Y') }}
                                </p>
                            </div>
                            <form action="{{ route('subscription.resume') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-green-800 transition-colors bg-green-100 rounded-md hover:bg-green-200 dark:bg-green-800 dark:text-green-100 dark:hover:bg-green-700">
                                    Resume Subscription
                                </button>
                            </form>
                        </div>
                        @endif

                        @if($subscription->paused())
                        <div class="flex items-center justify-between p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <div>
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                    Resume Subscription
                                </h3>
                                <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                    Reactivate your paused subscription
                                </p>
                            </div>
                            <form action="{{ route('subscription.resume') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-blue-800 transition-colors bg-blue-100 rounded-md hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700">
                                    Resume Now
                                </button>
                            </form>
                        </div>
                        @endif

                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                Need Help?
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                If you have any questions about your subscription or billing, please don't hesitate to
                                <button @click="$dispatch('show-help-modal')" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                                    contact our support team
                                </button>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="px-4 py-12 text-center sm:p-12">
                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                    No Active Subscription
                </h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    You don't have an active subscription. Subscribe to unlock premium features.
                </p>
                <div class="mt-6">
                    <a href="{{ route('subscription.plans') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 rounded-md hover:bg-blue-700">
                        View Available Plans
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('{{ session('success') }}', 'success');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('{{ session('error') }}', 'error');
    });
</script>
@endif
@endsection

