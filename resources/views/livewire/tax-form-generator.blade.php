<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Tax Forms</h2>
        <p class="text-gray-600 mt-1">Download your tax documents (1099-NEC) for IRS filing</p>
    </div>

    <!-- Year Selector -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Select Tax Year</label>
        <select
            wire:model="selectedYear"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
            @foreach($availableYears as $year)
            <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <!-- Loading State -->
    @if($isLoading)
    <div class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>
    @else
        <!-- Tax Forms -->
        @if(count($taxForms) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($taxForms as $form)
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $form['name'] ?? 'Tax Form' }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $form['description'] ?? 'Tax document for filing' }}</p>
                        <div class="mt-3 space-y-1">
                            <p class="text-sm"><span class="text-gray-600">Total Income:</span> <span class="font-medium text-gray-900">${{ $form['total_income'] ?? '0.00' }}</span></p>
                            <p class="text-sm"><span class="text-gray-600">Tax Year:</span> <span class="font-medium text-gray-900">{{ $selectedYear }}</span></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button
                        wire:click="downloadTaxForm('{{ $form['type'] ?? 'form' }}')"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition"
                    >
                        Download {{ $form['name'] ?? 'Form' }}
                    </button>
                </div>

                @if(isset($form['status']))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-xs"><span class="text-gray-600">Status:</span> <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium @if($form['status'] === 'ready') bg-green-100 text-green-800 @elseif($form['status'] === 'processing') bg-yellow-100 text-yellow-800 @else bg-gray-100 text-gray-800 @endif">{{ ucfirst($form['status']) }}</span></p>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-blue-900">Important Tax Information</h4>
            <ul class="mt-2 space-y-1 text-sm text-blue-800">
                <li class="flex items-start"><span class="mr-2">•</span> Tax forms are generated for all creators earning $600+ in a calendar year</li>
                <li class="flex items-start"><span class="mr-2">•</span> Forms are typically available by January 31st</li>
                <li class="flex items-start"><span class="mr-2">•</span> Keep a copy for your tax filing and records</li>
                <li class="flex items-start"><span class="mr-2">•</span> Contact support if you have any questions</li>
            </ul>
        </div>
        @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm">
            <p class="text-gray-600">No tax forms available for {{ $selectedYear }}</p>
            <p class="text-sm text-gray-500 mt-2">Tax forms are generated when annual earnings reach $600</p>
        </div>
        @endif
    @endif
</div>
