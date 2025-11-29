<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Affiliate Earnings</h3>
        <select
            wire:model="timeRange"
            class="px-3 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
            <option value="7days">7 Days</option>
            <option value="30days">30 Days</option>
            <option value="90days">90 Days</option>
            <option value="all">All Time</option>
        </select>
    </div>

    @if($isLoading)
    <div class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    @else
        <!-- Earnings Summary -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                <p class="text-sm text-green-700">Total Earned</p>
                <p class="text-2xl font-bold text-green-900 mt-1">${{ $earningsData['total_earned'] ?? '0.00' }}</p>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                <p class="text-sm text-blue-700">Pending</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">${{ $earningsData['pending'] ?? '0.00' }}</p>
            </div>
        </div>

        <!-- Earnings Breakdown -->
        @if(isset($earningsData['breakdown']))
        <div class="space-y-2">
            @foreach($earningsData['breakdown'] as $item)
            <div class="flex justify-between items-center pb-2 border-b border-gray-200 last:border-b-0">
                <span class="text-sm text-gray-700">{{ $item['name'] ?? 'Item' }}</span>
                <span class="text-sm font-semibold text-gray-900">${{ $item['amount'] ?? '0.00' }}</span>
            </div>
            @endforeach
        </div>
        @endif
    @endif
</div>
