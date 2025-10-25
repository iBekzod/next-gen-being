@props(['message' => 'This is premium content. Subscribe to read the full article.', 'post' => null])

<div class="relative my-12">
    <!-- Gradient fade overlay -->
    <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-transparent to-white dark:to-gray-900 pointer-events-none"></div>

    <!-- Paywall content -->
    <div class="relative z-10 max-w-2xl mx-auto text-center px-4 pt-16 pb-12 bg-white dark:bg-gray-900">
        <!-- Lock Icon -->
        <div class="inline-flex items-center justify-center w-16 h-16 mb-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>

        <!-- Premium Badge -->
        <div class="inline-flex items-center px-4 py-1 mb-4 text-sm font-semibold text-purple-600 bg-purple-100 dark:bg-purple-900 dark:text-purple-300 rounded-full">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
            Premium Content
        </div>

        <!-- Message -->
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
            Continue Reading with Premium
        </h3>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-lg mx-auto">
            {{ $message }}
        </p>

        <!-- Benefits -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8 max-w-3xl mx-auto">
            <div class="flex flex-col items-center p-4">
                <div class="w-10 h-10 mb-2 text-blue-600 dark:text-blue-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Unlimited Access</p>
            </div>
            <div class="flex flex-col items-center p-4">
                <div class="w-10 h-10 mb-2 text-blue-600 dark:text-blue-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Premium Articles</p>
            </div>
            <div class="flex flex-col items-center p-4">
                <div class="w-10 h-10 mb-2 text-blue-600 dark:text-blue-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Ad-Free Reading</p>
            </div>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="{{ route('subscription.plans') }}"
               class="inline-flex items-center justify-center px-8 py-3 text-base font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
                Upgrade to Premium
            </a>

            @guest
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center px-8 py-3 text-base font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">
                Sign In
            </a>
            @endguest
        </div>

        <!-- Trial Info -->
        <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
            Start with a 7-day free trial • Cancel anytime • No credit card required
        </p>
    </div>
</div>
