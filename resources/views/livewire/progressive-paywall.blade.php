<div>
    @if($showPaywall)
        <div class="relative">
            <!-- Content Preview (30%) -->
            <div class="max-h-96 overflow-hidden relative">
                <div class="prose dark:prose-invert max-w-none">
                    {!! Str::limit($post->content, 800, '...') !!}
                </div>

                <!-- Gradient Fade -->
                <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-white dark:from-gray-900 to-transparent"></div>
            </div>

            <!-- Paywall Overlay -->
            <div class="mt-8 p-8 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl border-2 border-blue-200 dark:border-blue-800">
                <div class="text-center max-w-2xl mx-auto">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        Unlock This {{ $tierDisplayName }} Article
                    </h3>

                    @if($remainingFreeArticles > 0)
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            You have <span class="font-bold text-blue-600">{{ $remainingFreeArticles }}</span> free premium {{ Str::plural('article', $remainingFreeArticles) }} remaining this month.
                        </p>
                    @else
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            You've reached your free article limit. Subscribe to unlock unlimited access to all premium content.
                        </p>
                    @endif

                    <!-- Social Proof -->
                    <div class="flex items-center justify-center space-x-6 mb-6 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                            <span>Join 12,543+ readers</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>7-day free trial</span>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="clickUpgrade"
                                class="px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-lg transition-all transform hover:scale-105">
                            Start Free Trial - {{ $tierPrice }}/mo after
                        </button>

                        @auth
                        <button wire:click="dismiss"
                                class="px-6 py-4 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            Not now
                        </button>
                        @else
                        <a href="{{ route('login') }}"
                           class="px-6 py-4 text-blue-600 dark:text-blue-400 hover:underline">
                            Already a member? Sign in
                        </a>
                        @endauth
                    </div>

                    <!-- Benefits -->
                    <div class="mt-8 grid grid-cols-2 gap-4 text-left text-sm">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Unlimited premium articles</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Ad-free reading</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Weekly newsletter</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Cancel anytime</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Full Content -->
        <div class="prose dark:prose-invert max-w-none">
            {!! $post->content !!}
        </div>
    @endif
</div>
