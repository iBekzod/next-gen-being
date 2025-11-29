<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Invoices & Receipts</h2>
            <p class="text-gray-600 mt-1">Download your payment invoices and receipts</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Search -->
            <input 
                type="text" 
                placeholder="Search invoices..." 
                wire:model.debounce.500ms="search"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />

            <!-- Status Filter -->
            <select 
                wire:model="filterStatus"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="all">All Statuses</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>

    <!-- Loading State -->
    @if($isLoading)
    <div class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    @else
        <!-- Invoices Table -->
        @if(count($invoices) > 0)
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $invoice['id'] ?? '#' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $invoice['date'] ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ $invoice['amount'] ?? '0.00' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ 
                                match($invoice['status'] ?? 'pending') {
                                    'paid' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                }
                            }}">
                                {{ ucfirst($invoice['status'] ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button 
                                wire:click="selectInvoice({{ $invoice['id'] }})"
                                class="text-blue-600 hover:text-blue-800 font-medium"
                            >
                                View
                            </button>
                            <a 
                                href="/api/v1/invoices/payout/{{ $invoice['id'] }}/download"
                                target="_blank"
                                class="text-green-600 hover:text-green-800 font-medium"
                            >
                                Download
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm">
            <p class="text-gray-600">No invoices found</p>
        </div>
        @endif
    @endif
</div>
