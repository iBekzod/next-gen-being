<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Webhooks</h2>
            <p class="text-gray-600 mt-1">Receive real-time event notifications for your account</p>
        </div>
        <button
            wire:click="toggleCreateForm"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium"
        >
            {{ $showCreateForm ? 'Cancel' : '+ Add Webhook' }}
        </button>
    </div>

    <!-- Success/Error Messages -->
    @if(session()->has('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-800">
        {{ session('success') }}
    </div>
    @endif

    @if(session()->has('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800">
        {{ session('error') }}
    </div>
    @endif

    <!-- Create Form -->
    @if($showCreateForm)
    <div class="bg-white rounded-lg shadow-sm p-6 border-2 border-blue-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Create New Webhook</h3>

        <div class="space-y-4">
            <!-- URL Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Webhook URL</label>
                <input
                    type="url"
                    wire:model="webhookData.url"
                    placeholder="https://example.com/webhook"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <p class="text-xs text-gray-500 mt-1">We'll send POST requests to this URL</p>
            </div>

            <!-- Events Selector -->
            @livewire('webhook-event-selector')

            <!-- Active Toggle -->
            <div class="flex items-center gap-3">
                <input
                    type="checkbox"
                    wire:model="webhookData.active"
                    id="webhook-active"
                    class="rounded border-gray-300"
                />
                <label for="webhook-active" class="text-sm font-medium text-gray-700">
                    Enable this webhook immediately
                </label>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3 pt-4">
                <button
                    wire:click="createWebhook"
                    class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium"
                >
                    Create Webhook
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Webhooks List -->
    @if($isLoading)
    <div class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>
    @else
        @if(count($webhooks) > 0)
        <div class="space-y-3">
            @foreach($webhooks as $webhook)
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="font-semibold text-gray-900 break-all">{{ $webhook['url'] ?? 'Webhook' }}</h3>
                            <span class="inline-flex px-2 py-1 rounded text-xs font-medium {{ $webhook['active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $webhook['active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <!-- Events -->
                        <div class="mt-2">
                            <p class="text-xs text-gray-600 mb-2">Subscribed Events:</p>
                            <div class="flex flex-wrap gap-1">
                                @if(isset($webhook['events']) && is_array($webhook['events']))
                                    @foreach($webhook['events'] as $event)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                        {{ $event }}
                                    </span>
                                    @endforeach
                                @else
                                <span class="text-xs text-gray-500">No events selected</span>
                                @endif
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                            <div>
                                <span class="text-gray-600">Created:</span>
                                <p class="font-medium text-gray-900">{{ $webhook['created_at'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Last Triggered:</span>
                                <p class="font-medium text-gray-900">{{ $webhook['last_triggered_at'] ?? 'Never' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button
                            wire:click="toggleWebhookActive({{ $webhook['id'] }}, {{ $webhook['active'] ? 'true' : 'false' }})"
                            class="px-3 py-2 {{ $webhook['active'] ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }} rounded text-sm font-medium transition"
                        >
                            {{ $webhook['active'] ? 'Disable' : 'Enable' }}
                        </button>
                        <button
                            wire:click="deleteWebhook({{ $webhook['id'] }})"
                            class="px-3 py-2 bg-red-100 text-red-800 hover:bg-red-200 rounded text-sm font-medium transition"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Testing & Logs Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Testing Panel -->
            @livewire('webhook-testing-panel')

            <!-- Logs Browser -->
            @livewire('webhook-logs-browser')
        </div>
        @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm">
            <p class="text-gray-600">No webhooks configured yet</p>
            <p class="text-sm text-gray-500 mt-2">Create your first webhook to start receiving real-time events</p>
        </div>
        @endif
    @endif
</div>
