<div class="space-y-6">
    <!-- Header with Time Range Selector -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Earnings Summary</h2>
            <p class="text-gray-600 mt-1">Your total revenue from all sources</p>
        </div>
        <select
            wire:model="timeRange"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
            <option value="7days">Last 7 Days</option>
            <option value="30days">Last 30 Days</option>
            <option value="90days">Last 90 Days</option>
            <option value="1year">Last Year</option>
            <option value="all">All Time</option>
        </select>
    </div>

    <!-- Loading State -->
    @if($isLoading)
    <div class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>
    @else
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Earnings -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-sm p-6 border border-blue-200">
                <h3 class="text-sm font-medium text-blue-900">Total Earnings</h3>
                <p class="text-3xl font-bold text-blue-600 mt-2">${{ $summaryData['total'] ?? '0.00' }}</p>
                <p class="text-xs text-blue-700 mt-2">{{ $timeRange }}</p>
            </div>

            <!-- Subscriptions -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm p-6 border border-green-200">
                <h3 class="text-sm font-medium text-green-900">Subscriptions</h3>
                <p class="text-3xl font-bold text-green-600 mt-2">${{ $summaryData['subscriptions'] ?? '0.00' }}</p>
                <p class="text-xs text-green-700 mt-2">Recurring revenue</p>
            </div>

            <!-- Tips & Donations -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-sm p-6 border border-purple-200">
                <h3 class="text-sm font-medium text-purple-900">Tips & Donations</h3>
                <p class="text-3xl font-bold text-purple-600 mt-2">${{ $summaryData['tips'] ?? '0.00' }}</p>
                <p class="text-xs text-purple-700 mt-2">Reader appreciation</p>
            </div>

            <!-- Premium Content -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-sm p-6 border border-orange-200">
                <h3 class="text-sm font-medium text-orange-900">Premium Content</h3>
                <p class="text-3xl font-bold text-orange-600 mt-2">${{ $summaryData['premium'] ?? '0.00' }}</p>
                <p class="text-xs text-orange-700 mt-2">Paywall revenue</p>
            </div>
        </div>

        <!-- Revenue Breakdown -->
        @if($summaryData && isset($summaryData['breakdown']))
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Breakdown</h3>
            <div class="space-y-3">
                @foreach($summaryData['breakdown'] as $source => $amount)
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $source)) }}</span>
                        <span class="text-sm font-semibold text-gray-900">${{ $amount }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($amount / ($summaryData['total'] ?? 1)) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-sm text-gray-600">Pending Payouts</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">${{ $summaryData['pending_payout'] ?? '0.00' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-sm text-gray-600">Average Per Day</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">${{ $summaryData['average_daily'] ?? '0.00' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-sm text-gray-600">Growth vs Last Period</p>
                <p class="text-2xl font-bold text-gray-900 mt-2 @if(isset($summaryData['growth']))@if($summaryData['growth'] >= 0) text-green-600 @else text-red-600 @endif@endif">
                    {{ $summaryData['growth'] ?? '0' }}%
                </p>
            </div>
        </div>
    @endif
</div>
