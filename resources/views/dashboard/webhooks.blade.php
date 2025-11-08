@extends('layouts.app')

@section('title', 'Webhooks - Dashboard')
@section('description', 'Manage webhooks, monitor deliveries, and configure event integrations')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">🔗 Webhooks</span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Webhook Management</h1>
        <p class="mt-4 text-base text-slate-300">Create and manage webhooks to integrate with external services. Monitor delivery status and logs.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <!-- Webhook Statistics -->
        <div class="grid grid-cols-1 gap-6 mb-12 sm:grid-cols-2 lg:grid-cols-5">
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Webhooks</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalWebhooks }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</p>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $activeWebhooks }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Failed</p>
                <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ $failedWebhooks }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Deliveries</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalDeliveries }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Success Rate</p>
                <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($overallSuccessRate, 1) }}%</p>
            </div>
        </div>

        <!-- Webhooks List -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Webhooks</h2>
                <a href="{{ route('webhooks.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Webhook
                </a>
            </div>

            @if($webhooks->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Name</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">URL</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Events</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Deliveries</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Success Rate</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Last Triggered</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($webhooks as $webhook)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $webhook->name }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 font-mono text-xs break-all max-w-xs">{{ Str::limit($webhook->url, 40) }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                @if($webhook->events)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($webhook->events, 0, 2) as $event)
                                        <span class="inline-block px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded">{{ $event }}</span>
                                        @endforeach
                                        @if(count($webhook->events) > 2)
                                        <span class="inline-block px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded">+{{ count($webhook->events) - 2 }}</span>
                                        @endif
                                    </div>
                                @else
                                <span class="text-gray-500 dark:text-gray-500">All events</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
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
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                @php
                                    $stats = $webhook->getStatistics();
                                @endphp
                                {{ $stats['total_calls'] }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-green-500" style="width: {{ $stats['success_rate'] }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium">{{ number_format($stats['success_rate'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm">
                                @if($webhook->last_triggered_at)
                                    {{ $webhook->last_triggered_at->format('M d, H:i') }}
                                @else
                                    <span class="text-gray-500">Never</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-3">
                                    <a href="{{ route('webhooks.show', $webhook) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium text-sm">
                                        View
                                    </a>
                                    <a href="{{ route('webhooks.edit', $webhook) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium text-sm">
                                        Edit
                                    </a>
                                    <button type="button" onclick="testWebhook({{ $webhook->id }})" class="text-amber-600 dark:text-amber-400 hover:underline font-medium text-sm">
                                        Test
                                    </button>
                                    <form action="{{ route('webhooks.destroy', $webhook) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline font-medium text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No webhooks yet</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Get started by creating your first webhook integration.</p>
                <a href="{{ route('webhooks.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Webhook
                </a>
            </div>
            @endif
        </div>

        <!-- Recent Delivery Logs -->
        @if($recentLogs->isNotEmpty())
        <div class="mt-12 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Deliveries</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Webhook</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Event</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Response Time</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Triggered At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($recentLogs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $log->webhook->name }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $log->event_type }}</td>
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
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm">
                                {{ $log->created_at->format('M d, H:i:s') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</section>

<script>
function testWebhook(webhookId) {
    if (!confirm('Send test payload to this webhook?')) {
        return;
    }

    const button = event.target;
    button.disabled = true;
    button.textContent = 'Testing...';

    fetch(`/api/webhooks/${webhookId}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Webhook test sent successfully!');
        } else {
            alert('Webhook test failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error testing webhook: ' + error.message);
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Test';
    });
}
</script>
@endsection
