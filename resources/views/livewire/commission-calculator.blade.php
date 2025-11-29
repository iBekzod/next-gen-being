<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Commission Calculator</h3>

    <div class="space-y-4">
        <!-- Sale Amount -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sale Amount</label>
            <div class="relative">
                <span class="absolute left-4 top-2 text-gray-600">$</span>
                <input
                    type="number"
                    wire:model="saleAmount"
                    placeholder="0.00"
                    step="0.01"
                    min="0"
                    class="w-full pl-7 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>
        </div>

        <!-- Commission Rate -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Commission Rate</label>
            <div class="relative">
                <input
                    type="number"
                    wire:model="commissionRate"
                    placeholder="10"
                    step="0.5"
                    min="0"
                    class="w-full pr-7 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <span class="absolute right-4 top-2 text-gray-600">%</span>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-4"></div>

        <!-- Result -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
            <p class="text-sm text-green-700 mb-1">Estimated Commission</p>
            <p class="text-3xl font-bold text-green-900">${{ number_format($estimatedCommission, 2) }}</p>
        </div>

        <!-- Breakdown -->
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Sale Amount:</span>
                <span class="font-medium text-gray-900">${{ number_format($saleAmount ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm border-t border-gray-200 pt-2">
                <span class="text-gray-600">Commission Rate:</span>
                <span class="font-medium text-gray-900">{{ $commissionRate }}%</span>
            </div>
            <div class="flex justify-between text-sm font-semibold text-green-700">
                <span>Your Earnings:</span>
                <span class="text-lg">${{ number_format($estimatedCommission, 2) }}</span>
            </div>
        </div>
    </div>
</div>
