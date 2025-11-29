<div class="space-y-4">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Test Webhook</h3>
        <p class="text-sm text-gray-600">Send a test event to your webhook</p>
    </div>

    <!-- Webhook Selector -->
    <select
        wire:model="selectedWebhook"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    >
        <option value="">Select a webhook to test...</option>
        @foreach($webhooks as $webhook)
        <option value="{{ $webhook['id'] }}">
            {{ $webhook['url'] ?? 'Webhook' }}
        </option>
        @endforeach
    </select>

    <!-- Test Button -->
    @if($selectedWebhook)
    <button
        wire:click="testWebhook({{ $selectedWebhook }})"
        @if($isTestingLoading) disabled @endif
        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg transition font-medium"
    >
        @if($isTestingLoading)
            Testing...
        @else
            Send Test Event
        @endif
    </button>
    @endif

    <!-- Test Response -->
    @if($testResponse)
    <div class="mt-4 p-4 rounded-lg {{ $testResponse['success'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
        <p class="text-sm font-medium {{ $testResponse['success'] ? 'text-green-900' : 'text-red-900' }}">
            {{ $testResponse['success'] ? '✓ Test Successful' : '✗ Test Failed' }}
        </p>
        <p class="text-xs {{ $testResponse['success'] ? 'text-green-700' : 'text-red-700' }} mt-1">
            {{ $testResponse['message'] }}
        </p>
        @if(isset($testResponse['status']) && !is_bool($testResponse['status']))
        <p class="text-xs {{ $testResponse['success'] ? 'text-green-700' : 'text-red-700' }} mt-1">
            Status: {{ $testResponse['status'] }}
        </p>
        @endif
    </div>
    @endif
</div>
