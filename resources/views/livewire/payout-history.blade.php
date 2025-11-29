<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Payout History</h2>
        <p class="text-gray-600 mt-1">Track your payment withdrawals and status</p>
    </div>

    <!-- Loading State -->
    @if($isLoading)
    <div class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>
    @else
        <!-- Payouts Timeline -->
        @if(count($payouts) > 0)
        <div class="space-y-4">
            @foreach($payouts as $payout)
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 @if(isset($payout['status']))@if($payout['status'] === 'completed') border-green-500 @elseif($payout['status'] === 'pending') border-yellow-500 @elseif($payout['status'] === 'failed') border-red-500 @else border-gray-500 @endif @else border-gray-500 @endif">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">${{ $payout['amount'] ?? '0.00' }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $payout['period'] ?? 'Payment Period' }}</p>
                    </div>
                    <div class="mt-4 sm:mt-0 text-right">
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium @if(isset($payout['status']))@if($payout['status'] === 'completed') bg-green-100 text-green-800 @elseif($payout['status'] === 'pending') bg-yellow-100 text-yellow-800 @elseif($payout['status'] === 'failed') bg-red-100 text-red-800 @else bg-gray-100 text-gray-800 @endif @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($payout['status'] ?? 'pending') }}
                        </span>
                        <p class="text-xs text-gray-500 mt-2">{{ $payout['date'] ?? 'Date pending' }}</p>
                    </div>
                </div>

                @if(isset($payout['details']))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Bank</span>
                            <p class="font-medium text-gray-900">{{ $payout['details']['bank'] ?? 'Bank' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-600">Account</span>
                            <p class="font-medium text-gray-900">{{ $payout['details']['account'] ?? 'Account' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-600">Reference</span>
                            <p class="font-medium text-gray-900">{{ $payout['details']['reference'] ?? 'Ref' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm">
            <p class="text-gray-600">No payouts yet</p>
            <p class="text-sm text-gray-500 mt-2">Your payouts will appear here once they are processed</p>
        </div>
        @endif
    @endif
</div>
