<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Your Affiliate Links</h3>
        <select
            wire:model="sortBy"
            class="px-3 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
            <option value="clicks">Most Clicks</option>
            <option value="conversions">Most Conversions</option>
            <option value="earnings">Highest Earnings</option>
            <option value="created">Newest First</option>
        </select>
    </div>

    @if($isLoading)
    <div class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    @else
        @if(count($links) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Link</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Clicks</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Conversions</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Earnings</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($links as $link)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <a href="{{ $link['url'] ?? '#' }}" target="_blank" class="text-blue-600 hover:text-blue-800 break-all text-xs">
                                {{ substr($link['url'] ?? 'Link', 0, 50) }}...
                            </a>
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $link['clicks'] ?? 0 }}</td>
                        <td class="px-4 py-3 font-medium">{{ $link['conversions'] ?? 0 }}</td>
                        <td class="px-4 py-3 font-medium text-green-600">${{ $link['earnings'] ?? '0.00' }}</td>
                        <td class="px-4 py-3">
                            <button
                                wire:click="copyLink({{ $link['id'] }}, '{{ $link['url'] }}')"
                                class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200 transition"
                            >
                                Copy
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <p>No affiliate links created yet</p>
        </div>
        @endif
    @endif
</div>
