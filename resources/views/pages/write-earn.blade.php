@extends('layouts.app')

@section('title', 'Write & Earn - Share Your Knowledge')
@section('description', 'Become a blogger on ' . setting('site_name', 'NextGenBeing') . ' and earn money by sharing your expertise with our community.')

@section('content')
<div class="min-h-screen bg-white dark:bg-gray-900">
    <!-- Hero Section -->
    <div class="relative px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl text-center">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
                Write & Earn Money
            </h1>
            <p class="mt-6 text-lg text-gray-600 dark:text-gray-300">
                Share your knowledge, build your audience, and earn revenue from your articles.
            </p>

            <!-- CTA Buttons -->
            <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center">
                <a href="{{ route('register') }}"
                    class="inline-flex items-center justify-center px-8 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    Sign Up for Free
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
                <a href="{{ route('login') }}"
                    class="inline-flex items-center justify-center px-8 py-3 font-semibold text-gray-700 dark:text-gray-300 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Sign In
                </a>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">How It Works</h2>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Four simple steps to start earning</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">1</span>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create Account</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sign up and set up your blogger profile in minutes.</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-green-600 dark:text-green-400">2</span>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Write Content</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Use our AI-powered editor to create engaging articles effortlessly.</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">3</span>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Publish & Share</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Publish to our community and share with your audience.</p>
                </div>

                <!-- Step 4 -->
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">4</span>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Earn Money</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Get paid based on views, engagement, and premium subscriptions.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">Why Write With Us?</h2>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Feature 1 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">AI-Powered Writing Assistant</h3>
                        <p class="text-gray-600 dark:text-gray-400">Get real-time suggestions for titles, content, and images to boost engagement.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Analytics & Insights</h3>
                        <p class="text-gray-600 dark:text-gray-400">Track your performance with detailed analytics on views, engagement, and earnings.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Multiple Revenue Streams</h3>
                        <p class="text-gray-600 dark:text-gray-400">Earn from views, premium subscriptions, and sponsored content opportunities.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Growing Community</h3>
                        <p class="text-gray-600 dark:text-gray-400">Connect with thousands of readers interested in your topics and expertise.</p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Easy Customization</h3>
                        <p class="text-gray-600 dark:text-gray-400">Customize your profile, topics, and content settings to match your style.</p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">24/7 Support</h3>
                        <p class="text-gray-600 dark:text-gray-400">Get help whenever you need it with our dedicated support team.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings Example -->
    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-4">Earning Potential</h2>
            <p class="text-center text-gray-600 dark:text-gray-400 mb-12">Example monthly earnings based on reader engagement</p>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Tier 1 -->
                <div class="bg-white dark:bg-gray-700 rounded-lg p-8 border-2 border-gray-200 dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Starting Out</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">500-1,000 views/month</p>
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-4">$50-150</div>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                        <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Baseline views revenue</li>
                        <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Build your audience</li>
                    </ul>
                </div>

                <!-- Tier 2 -->
                <div class="bg-white dark:bg-gray-700 rounded-lg p-8 border-2 border-blue-500 relative">
                    <div class="absolute -top-3 right-4 bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Popular</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Growing Creator</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">5,000-10,000 views/month</p>
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-4">$500-1,500</div>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                        <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>All starter benefits</li>
                        <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Engagement bonuses</li>
                        <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Premium revenue share</li>
                    </ul>
                </div>

                <!-- Tier 3 -->
                <div class="bg-white dark:bg-gray-700 rounded-lg p-8 border-2 border-gray-200 dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Established Author</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">50,000+ views/month</p>
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400 mb-4">$5,000+</div>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                        <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>All benefits included</li>
                        <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Sponsorship deals</li>
                        <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Priority support</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">Frequently Asked Questions</h2>

            <div class="space-y-8">
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full text-left p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Do I need experience to start writing?</h3>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'transform rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="p-4 text-gray-600 dark:text-gray-400">
                        No! Our platform welcomes writers of all skill levels. Whether you're a beginner or an experienced author, our AI writing assistant will help you create engaging content. The key is sharing knowledge you're passionate about.
                    </div>
                </div>

                <div x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full text-left p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">When do I get paid?</h3>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'transform rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="p-4 text-gray-600 dark:text-gray-400">
                        Earnings are calculated monthly and processed on the 15th of each month. You can withdraw to your bank account, PayPal, or other supported payment methods. Minimum payout is $10.
                    </div>
                </div>

                <div x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full text-left p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Can I write about any topic?</h3>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'transform rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="p-4 text-gray-600 dark:text-gray-400">
                        We support a wide range of topics including technology, business, education, lifestyle, science, and more. We do have content guidelines to ensure quality and safety for our community. Check our community standards for more details.
                    </div>
                </div>

                <div x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full text-left p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">What if my article doesn't get many views?</h3>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'transform rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="p-4 text-gray-600 dark:text-gray-400">
                        Don't worry! Every view counts. We also provide promotion opportunities, featured spots, and help with SEO to boost your visibility. Our AI-powered recommendations engine also promotes quality content naturally.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Final CTA -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-3xl font-bold text-white mb-6">Ready to Start Earning?</h2>
            <p class="text-blue-100 mb-8">Join thousands of writers who are already sharing their knowledge and making money on {{ setting('site_name', 'NextGenBeing') }}</p>
            <a href="{{ route('register') }}"
                class="inline-flex items-center px-8 py-4 font-semibold text-blue-600 bg-white rounded-lg hover:bg-gray-100 transition-colors">
                Create Your Account Today
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Alpine.js data is initialized with x-data attributes
</script>
@endpush
