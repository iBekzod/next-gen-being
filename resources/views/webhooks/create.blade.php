@extends('layouts.app')

@section('title', 'Create Webhook')
@section('description', 'Create a new webhook to integrate with external services')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-3xl">
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Create Webhook</h1>
        <p class="mt-4 text-base text-slate-300">Set up a new webhook to integrate with external services and receive real-time event notifications.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-3xl">
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <form action="{{ route('webhooks.store') }}" method="POST" class="p-8 space-y-8">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                        Webhook Name
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
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
                        value="{{ old('url') }}"
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
                                @if(in_array($eventKey, old('events', []))) checked @endif
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
                            @if(old('verify_ssl', true)) checked @endif
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

                <!-- Help Text -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2">💡 Webhook Payload</h4>
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        Each webhook event will be sent as a POST request with JSON payload containing event details.
                        Your endpoint should return a 2xx status code to confirm successful delivery.
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-6">
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Create Webhook
                    </button>
                    <a href="{{ route('dashboard.webhooks') }}" class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
