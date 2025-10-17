<div class="newsletter-subscribe {{ $compact ? 'compact' : 'full' }}">
    @if($subscribed)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold text-green-800 dark:text-green-200">Almost there!</p>
                    <p class="text-sm text-green-700 dark:text-green-300">Check your email to confirm your subscription.</p>
                </div>
            </div>
        </div>
    @else
        @if(!$compact)
            <div class="mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    Get the Best Articles in Your Inbox
                </h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    Join 10,000+ readers. No spam, unsubscribe anytime.
                </p>
            </div>
        @endif

        @if($error)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 mb-4">
                <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
            </div>
        @endif

        <form wire:submit.prevent="subscribe" class="space-y-3">
            <div>
                <input
                    type="email"
                    wire:model.defer="email"
                    placeholder="your@email.com"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    required
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            @if(!$compact)
                <div>
                    <select
                        wire:model.defer="frequency"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    >
                        <option value="weekly">Weekly Digest</option>
                        <option value="monthly">Monthly Roundup</option>
                        <option value="daily">Daily Updates</option>
                    </select>
                </div>
            @endif

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    Subscribe Now
                </span>
                <span wire:loading>
                    <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>

            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                By subscribing, you agree to our Privacy Policy.
            </p>
        </form>
    @endif
</div>
