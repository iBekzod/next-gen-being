@if($showBanner)
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-4 rounded-lg mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center flex-1">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <div class="font-bold">Your trial ends in {{ $daysLeft }} {{ Str::plural('day', $daysLeft) }}!</div>
                    <div class="text-sm opacity-90">Subscribe now to keep unlimited access to all premium content</div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('subscription.plans') }}"
                   class="px-6 py-2 bg-white text-orange-600 font-bold rounded-lg hover:bg-gray-100 transition flex-shrink-0">
                    Subscribe Now
                </a>
                <button wire:click="dismiss"
                        class="text-white hover:text-gray-200 flex-shrink-0">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif
