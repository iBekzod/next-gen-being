<div class="space-y-4">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
        <p class="text-sm text-gray-600">View webhook delivery logs and status</p>
    </div>

    <!-- Filter -->
    <select
        wire:model="filterStatus"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    >
        <option value="all">All Status</option>
        <option value="success">Success (200-299)</option>
        <option value="failed">Failed (400+)</option>
        <option value="pending">Pending</option>
    </select>

    <!-- Loading State -->
    @if($isLoading)
    <div class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    @else
        <!-- Logs Table -->
        @if(count($logs) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Timestamp</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Event</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Response</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-xs text-gray-600">{{ $log['created_at'] ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-xs font-medium text-gray-900">{{ $log['event'] ?? 'Unknown' }}</td>
                        <td class="px-4 py-2">
                            <span class="inline-flex px-2 py-1 rounded text-xs font-medium {{
                                isset($log['status_code']) && $log['status_code'] >= 200 && $log['status_code'] < 300
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-red-100 text-red-800'
                            }}">
                                {{ $log['status_code'] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-600 truncate">{{ substr($log['response'] ?? '', 0, 50) }}...</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 bg-gray-50 rounded-lg">
            <p class="text-gray-600">No logs available yet</p>
        </div>
        @endif
    @endif
</div>
