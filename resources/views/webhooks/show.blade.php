@extends('layouts.app')

@section('title', 'Webhook Details - ' . $webhook->name)
@section('description', 'View webhook details, statistics, and delivery logs')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ $webhook->name }}</h1>
                <p class="mt-4 text-base text-slate-300">Webhook details, statistics, and delivery history.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('webhooks.edit', $webhook) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    Edit
                </a>
                <a href="{{ route('dashboard.webhooks') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-lg transition">
                    Back
                </a>
            </div>
        </div>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl space-y-12">
        <!-- Webhook Configuration -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Configuration</h2>
            </div>
            <div class="px-6 py-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Webhook URL</p>
                        <p class="mt-2 font-mono text-sm text-gray-900 dark:text-white break-all">{{ $webhook->url }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                        <p class="mt-2">
                            @if($webhook->status === 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                ✓ Active
                            </span>
                            @elseif($webhook->status === 'failed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                ✗ Failed
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                ⊝ {{ ucfirst($webhook->status) }}
                            </span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">SSL Verification</p>
                        <p class="mt-2 font-medium text-gray-900 dark:text-white">
                            {{ $webhook->verify_ssl ? '✓ Enabled' : '✗ Disabled' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Created At</p>
                        <p class="mt-2 font-medium text-gray-900 dark:text-white">
                            {{ $webhook->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Subscribed Events</p>
                    @if($webhook->events && count($webhook->events) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($webhook->events as $event)
                        <span class="inline-block px-3 py-1 text-sm bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-full">
                            {{ $event }}
                        </span>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">All events</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Deliveries</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $statistics['total_calls'] }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Successful</p>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $statistics['successful_calls'] }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Failed</p>
                <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ $statistics['failed_calls'] }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Success Rate</p>
                <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($statistics['success_rate'], 1) }}%
                </p>
            </div>
        </div>

        <!-- Delivery History -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Deliveries</h2>
            </div>

            @if($recentLogs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Event</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Response Time</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">HTTP Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Delivered At</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($recentLogs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $log->event_type }}</td>
                            <td class="px-6 py-4">
                                @if($log->success)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                    ✓ Success
                                </span>
                                @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                    ✗ Failed
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                @if($log->response_time_ms)
                                    {{ $log->response_time_ms }}ms
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                @if($log->response_status)
                                    <span class="font-mono text-sm">{{ $log->response_status }}</span>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm">
                                {{ $log->created_at->format('M d, H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    type="button"
                                    onclick="toggleLogDetails(event, 'log-{{ $log->id }}')"
                                    class="text-blue-600 dark:text-blue-400 hover:underline text-sm font-medium"
                                >
                                    View
                                </button>
                            </td>
                        </tr>
                        <tr class="hidden bg-gray-50 dark:bg-slate-700/50" id="log-{{ $log->id }}">
                            <td colspan="6" class="px-6 py-4">
                                <div class="space-y-4">
                                    @if($log->error_message)
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Error Message:</p>
                                        <p class="mt-1 text-sm font-mono text-red-600 dark:text-red-400">{{ $log->error_message }}</p>
                                    </div>
                                    @endif

                                    @if($log->request_payload)
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Request Payload:</p>
                                        <pre class="mt-1 p-3 bg-gray-900 text-gray-100 rounded text-xs overflow-auto max-h-48"><code>{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                    </div>
                                    @endif

                                    @if($log->response_body)
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Response Body:</p>
                                        <pre class="mt-1 p-3 bg-gray-900 text-gray-100 rounded text-xs overflow-auto max-h-48"><code>{{ $log->response_body }}</code></pre>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">
                No delivery logs yet
            </div>
            @endif
        </div>
    </div>
</section>

<script>
function toggleLogDetails(event, logId) {
    event.preventDefault();
    const element = document.getElementById(logId);
    element.classList.toggle('hidden');
}
</script>
@endsection
