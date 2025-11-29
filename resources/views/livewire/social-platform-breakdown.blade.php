<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Shares by Platform</h3>

    <div class="space-y-4">
        @foreach($platformData as $platform)
        <div class="flex items-center gap-4">
            <!-- Platform Icon & Name -->
            <div class="flex items-center gap-2 min-w-32">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-600">
                    {{ $platform['icon'] }}
                </div>
                <span class="text-sm font-medium text-gray-900">{{ $platform['name'] }}</span>
            </div>

            <!-- Share Count -->
            <div class="flex-1">
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div
                        class="h-3 rounded-full bg-gradient-to-r from-blue-500 to-blue-600"
                        style="width: {{ isset($platform['shares']) && $platform['shares'] > 0 ? min(100, ($platform['shares'] / max(1, array_sum(array_column($platformData, 'shares')))) * 100) : 0 }}%"
                    ></div>
                </div>
            </div>

            <!-- Share Number -->
            <div class="min-w-16 text-right">
                <p class="text-lg font-semibold text-gray-900">{{ $platform['shares'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">shares</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Total -->
    <div class="mt-6 pt-4 border-t border-gray-200">
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Total Shares</span>
            <span class="text-lg font-bold text-gray-900">{{ array_sum(array_column($platformData, 'shares')) }}</span>
        </div>
    </div>
</div>
