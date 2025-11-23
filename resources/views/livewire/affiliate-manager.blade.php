<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <p class="text-sm text-gray-600">Active Links</p>
            <p class="text-3xl font-bold text-blue-600">{{ $links->count() ?? 0 }}</p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
            <p class="text-sm text-gray-600">Total Clicks</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['total_clicks'] ?? 0 }}</p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
            <p class="text-sm text-gray-600">Conversions</p>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['conversions'] ?? 0 }}</p>
        </div>
        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
            <p class="text-sm text-gray-600">Earnings</p>
            <p class="text-3xl font-bold text-yellow-600">${{ number_format($totalEarnings, 2) }}</p>
        </div>
    </div>

    <!-- Create Link Button -->
    <button 
        wire:click="createLink"
        class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
    >
        ➕ Create New Link
    </button>

    @if($isLoading)
        <div class="text-center text-gray-500">Loading...</div>
    @else
        <!-- Affiliate Links -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Clicks</th>
                        <th class="px-4 py-3 text-left">Conversions</th>
                        <th class="px-4 py-3 text-left">Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($links as $link)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-sm">{{ $link->code }}</td>
                            <td class="px-4 py-3">{{ $link->clicks->count() }}</td>
                            <td class="px-4 py-3">{{ $link->conversions->count() }}</td>
                            <td class="px-4 py-3 font-semibold">${{ number_format($link->conversions->sum('commission'), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">No affiliate links yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
