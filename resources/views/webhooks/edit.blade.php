@extends('layouts.app')

@section('title', 'Edit Webhook')
@section('description', 'Edit webhook configuration and event subscriptions')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-3xl">
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Edit Webhook</h1>
        <p class="mt-4 text-base text-slate-300">Update webhook configuration and event subscriptions.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-3xl">
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <form action="{{ route('webhooks.update', $webhook) }}" method="POST" class="p-8 space-y-8">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                        Webhook Name
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $webhook->name) }}"
                        placeholder="e.g., My App Integration"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('name') border-red-500 @enderror"
                        required
                    >
                    @error('name')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- URL -->
                <div>
                    <label for="url" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                        Webhook URL
                    </label>
                    <input
                        type="url"
                        id="url"
                        name="url"
                        value="{{ old('url', $webhook->url) }}"
                        placeholder="https://example.com/webhook"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('url') border-red-500 @enderror"
                        required
                    >
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        The URL where we'll send webhook events. Must be HTTPS.
                    </p>
                    @error('url')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                        Status
                    </label>
                    <div class="p-3 border border-gray-200 dark:border-slate-700 rounded-lg bg-gray-50 dark:bg-slate-900">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            Current Status:
                            <span class="inline-block ml-2 px-3 py-1 rounded-full text-sm font-medium
                                @if($webhook->status === 'active')
                                    bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                @elseif($webhook->status === 'failed')
                                    bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                                @else
                                    bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                @endif
                            ">
                                {{ ucfirst($webhook->status) }}
                            </span>
                        </p>
                        @if($webhook->status === 'failed')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                            Last error: {{ $webhook->last_error }}
                        </p>
                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                            Retry count: {{ $webhook->retry_count }} / {{ $webhook->max_retries }}
                        </p>
                        @endif
                    </div>
                </div>

                <!-- Events -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-4">
                        Events
                    </label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Select which events should trigger this webhook. Leave empty to subscribe to all events.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($availableEvents as $eventKey => $eventLabel)
                        <label class="flex items-center p-3 border border-gray-200 dark:border-slate-700 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700/50 cursor-pointer transition">
                            <input
                                type="checkbox"
                                name="events[]"
                                value="{{ $eventKey }}"
                                @if(in_array($eventKey, old('events', $webhook->events ?? []))) checked @endif
                                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                            >
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">{{ $eventLabel }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- SSL Verification -->
                <div>
                    <label class="flex items-center p-4 border border-gray-200 dark:border-slate-700 rounded-lg">
                        <input
                            type="checkbox"
                            name="verify_ssl"
                            value="1"
                            @if(old('verify_ssl', $webhook->verify_ssl)) checked @endif
                            class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                        >
                        <span class="ml-3 flex-1">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Verify SSL Certificate</span>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Verify the SSL certificate of the webhook URL. Recommended for production.
                            </p>
                        </span>
                    </label>
                </div>

                <!-- Statistics -->
                <div class="bg-gray-50 dark:bg-slate-900 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">📊 Webhook Statistics</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Total Deliveries</p>
                            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $webhook->logs()->count() }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Last Triggered</p>
                            <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                @if($webhook->last_triggered_at)
                                    {{ $webhook->last_triggered_at->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-6">
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Save Changes
                    </button>
                    <a href="{{ route('dashboard.webhooks') }}" class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="mt-8 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-red-200 dark:border-red-900 overflow-hidden">
            <div class="px-8 py-6 border-b border-red-200 dark:border-red-900">
                <h3 class="text-lg font-semibold text-red-900 dark:text-red-300">Danger Zone</h3>
            </div>
            <div class="px-8 py-6">
                <form action="{{ route('webhooks.destroy', $webhook) }}" method="POST" onsubmit="return confirm('Are you sure? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                        Delete Webhook
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
