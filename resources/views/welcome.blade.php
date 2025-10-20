<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Next Gen Being - Personal Development & AI Insights</title>
    <meta name="description" content="Unlock your potential with premium content on personal development, AI, consciousness, and human potential.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://app.lemonsqueezy.com/js/lemon.js" defer></script>
</head>
<body class="antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50">
    <!-- Header -->
    <nav class="bg-white/80 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Next Gen Being
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#pricing" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition">Pricing</a>
                    <a href="#features" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition">Features</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition">Login</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center">
                <div class="inline-block mb-4 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">
                    🚀 Platform Launching Soon
                </div>
                <h2 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-6">
                    Elevate Your Mind.<br/>
                    <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Transform Your Life.
                    </span>
                </h2>
                <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                    Join a community exploring personal development, AI, consciousness, and human potential through curated insights and exclusive content.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#pricing" class="bg-indigo-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl">
                        View Pricing Plans
                    </a>
                    <a href="#features" class="bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition border-2 border-indigo-600">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    What You'll Get
                </h3>
                <p class="text-lg text-gray-600">
                    Premium content and tools to accelerate your personal growth
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-8 rounded-2xl border-2 border-gray-200 hover:border-indigo-300 transition">
                    <div class="text-4xl mb-4">📚</div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Exclusive Articles</h4>
                    <p class="text-gray-600">
                        In-depth articles on personal development, AI trends, consciousness exploration, and cutting-edge technology.
                    </p>
                </div>

                <div class="p-8 rounded-2xl border-2 border-gray-200 hover:border-indigo-300 transition">
                    <div class="text-4xl mb-4">🤖</div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">AI-Powered Insights</h4>
                    <p class="text-gray-600">
                        Get personalized learning recommendations and insights powered by advanced AI technology.
                    </p>
                </div>

                <div class="p-8 rounded-2xl border-2 border-gray-200 hover:border-indigo-300 transition">
                    <div class="text-4xl mb-4">📧</div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Weekly Newsletter</h4>
                    <p class="text-gray-600">
                        Curated digest of the best content, insights, and resources delivered to your inbox every week.
                    </p>
                </div>

                <div class="p-8 rounded-2xl border-2 border-gray-200 hover:border-indigo-300 transition">
                    <div class="text-4xl mb-4">💡</div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Tutorials & Guides</h4>
                    <p class="text-gray-600">
                        Step-by-step tutorials on practical skills, tools, and techniques for personal transformation.
                    </p>
                </div>

                <div class="p-8 rounded-2xl border-2 border-gray-200 hover:border-indigo-300 transition">
                    <div class="text-4xl mb-4">🎯</div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Resource Library</h4>
                    <p class="text-gray-600">
                        Downloadable templates, checklists, and resources to support your growth journey.
                    </p>
                </div>

                <div class="p-8 rounded-2xl border-2 border-gray-200 hover:border-indigo-300 transition">
                    <div class="text-4xl mb-4">👥</div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Community Access</h4>
                    <p class="text-gray-600">
                        Connect with like-minded individuals on a journey of continuous learning and growth.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Choose Your Plan
                </h3>
                <p class="text-lg text-gray-600">
                    Select the perfect plan for your growth journey
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Basic Tier -->
                <div class="bg-white rounded-2xl shadow-lg p-8 border-2 border-gray-200 hover:border-indigo-300 transition">
                    <div class="text-center">
                        <h4 class="text-2xl font-bold text-gray-900 mb-2">Basic</h4>
                        <div class="mb-6">
                            <span class="text-4xl font-extrabold text-gray-900">$9.99</span>
                            <span class="text-gray-600">/month</span>
                        </div>
                        <p class="text-gray-600 mb-6">Perfect for individual learners</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Access to premium articles</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Weekly newsletter digest</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Community access</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Basic tutorials</span>
                        </li>
                    </ul>
                    <a href="https://{{ config('services.lemonsqueezy.store_domain') }}/buy/{{ config('services.lemonsqueezy.basic_variant_id') }}"
                       class="lemonsqueezy-button w-full bg-gray-100 text-gray-900 py-3 rounded-lg font-semibold hover:bg-gray-200 transition block text-center">
                        Start Monthly Subscription
                    </a>
                </div>

                <!-- Pro Tier (Featured) -->
                <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl shadow-2xl p-8 border-2 border-indigo-600 transform md:scale-105 relative">
                    <div class="absolute top-0 right-0 bg-yellow-400 text-gray-900 px-4 py-1 rounded-bl-lg rounded-tr-lg text-sm font-bold">
                        POPULAR
                    </div>
                    <div class="text-center">
                        <h4 class="text-2xl font-bold text-white mb-2">Pro</h4>
                        <div class="mb-6">
                            <span class="text-4xl font-extrabold text-white">$19.99</span>
                            <span class="text-indigo-200">/month</span>
                        </div>
                        <p class="text-indigo-100 mb-6">For serious growth seekers</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-white font-medium">Everything in Basic</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-white">AI-powered insights</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-white">Advanced tutorials</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-white">Downloadable resources</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-white">Priority support</span>
                        </li>
                    </ul>
                    <a href="https://{{ config('services.lemonsqueezy.store_domain') }}/buy/{{ config('services.lemonsqueezy.pro_variant_id') }}"
                       class="lemonsqueezy-button w-full bg-white text-indigo-600 py-3 rounded-lg font-semibold hover:bg-gray-100 transition block text-center">
                        Start Monthly Subscription
                    </a>
                </div>

                <!-- Team Tier -->
                <div class="bg-white rounded-2xl shadow-lg p-8 border-2 border-gray-200 hover:border-indigo-300 transition">
                    <div class="text-center">
                        <h4 class="text-2xl font-bold text-gray-900 mb-2">Team</h4>
                        <div class="mb-6">
                            <span class="text-4xl font-extrabold text-gray-900">$49.99</span>
                            <span class="text-gray-600">/month</span>
                        </div>
                        <p class="text-gray-600 mb-6">For teams and organizations</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Everything in Pro</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Up to 5 team members</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Team collaboration tools</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Dedicated support</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Custom integrations</span>
                        </li>
                    </ul>
                    <a href="https://{{ config('services.lemonsqueezy.store_domain') }}/buy/{{ config('services.lemonsqueezy.team_variant_id') }}"
                       class="lemonsqueezy-button w-full bg-gray-100 text-gray-900 py-3 rounded-lg font-semibold hover:bg-gray-200 transition block text-center">
                        Start Monthly Subscription
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter CTA -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-indigo-600 to-purple-600">
        <div class="max-w-4xl mx-auto text-center">
            <h3 class="text-3xl md:text-4xl font-bold text-white mb-4">
                Stay Updated on Our Launch
            </h3>
            <p class="text-xl text-indigo-100 mb-8">
                Be the first to know when we go live. Join our newsletter for exclusive early access.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
                <input
                    type="email"
                    placeholder="Enter your email"
                    class="flex-1 px-6 py-4 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white"
                >
                <button class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Subscribe
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h5 class="text-white font-bold text-lg mb-4">Next Gen Being</h5>
                    <p class="text-sm text-gray-400">
                        Empowering personal growth through curated insights on AI, consciousness, and human potential.
                    </p>
                </div>
                <div>
                    <h6 class="text-white font-semibold mb-4">Platform</h6>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="#pricing" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition">About</a></li>
                    </ul>
                </div>
                <div>
                    <h6 class="text-white font-semibold mb-4">Resources</h6>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition">Tutorials</a></li>
                        <li><a href="#" class="hover:text-white transition">Newsletter</a></li>
                    </ul>
                </div>
                <div>
                    <h6 class="text-white font-semibold mb-4">Contact</h6>
                    <ul class="space-y-2 text-sm">
                        <li><a href="mailto:erkinovbegzod.45@gmail.com" class="hover:text-white transition">Email Us</a></li>
                        <li class="text-gray-400">Platform in Development</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
                <p>&copy; 2025 Next Gen Being. All rights reserved.</p>
                <p class="mt-2">Powered by Laravel & Lemon Squeezy</p>
            </div>
        </div>
    </footer>
</body>
</html>
