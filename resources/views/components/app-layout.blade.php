<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeSwitcher()" x-init="init()"
    x-effect="document.documentElement.classList.toggle('dark', darkMode)">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@isset($title){{ $title }}@else{{ setting('site_name', 'NextGenBeing') }}@endisset</title>
    <meta name="description" content="@isset($description){{ $description }}@else{{ setting('site_description') }}@endisset">

    <!-- Favicon and Site Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('uploads/logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('uploads/logo.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('uploads/logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('uploads/logo.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#1f2937">
    <meta name="msapplication-TileColor" content="#1f2937">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script>
        // Pre-load dark mode to prevent flash
        (function() {
            const stored = localStorage.getItem("theme") ?? localStorage.getItem("darkMode");
            const prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
            if ((stored === "dark" || stored === "true") || (!stored && prefersDark)) {
                document.documentElement.classList.add("dark");
            }
        })();

        // Define Alpine.js data functions before Alpine loads
        window.themeSwitcher = function() {
            return {
                darkMode: true,
                init() {
                    const stored = localStorage.getItem('theme') ?? localStorage.getItem('darkMode');
                    if (stored) {
                        this.darkMode = stored === 'dark' || stored === 'true';
                    } else {
                        // Default to dark mode
                        this.darkMode = true;
                    }

                    document.documentElement.classList.toggle('dark', this.darkMode);

                    this.$watch('darkMode', (value) => {
                        document.documentElement.classList.toggle('dark', value);
                        localStorage.setItem('theme', value ? 'dark' : 'light');
                        localStorage.removeItem('darkMode');

                        if (window.Alpine?.store('app')) {
                            window.Alpine.store('app').darkMode = value;
                        }
                    });
                },
                toggle() {
                    this.darkMode = !this.darkMode;
                }
            }
        };

        window.searchModal = function() {
            return {
                isOpen: false,
                searchQuery: '',
                results: {
                    posts: [],
                    tutorials: [],
                    bloggers: []
                },
                isLoading: false,
                async performSearch() {
                    if (!this.searchQuery.trim()) {
                        this.results = { posts: [], tutorials: [], bloggers: [] };
                        return;
                    }

                    this.isLoading = true;
                    try {
                        const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(this.searchQuery)}`);
                        const suggestions = await response.json();

                        // Organize flat array of suggestions by type
                        const categorized = { posts: [], tutorials: [], bloggers: [] };

                        suggestions.forEach(item => {
                            if (item.type === 'post') {
                                categorized.posts.push(item);
                            } else if (item.type === 'tutorial') {
                                categorized.tutorials.push(item);
                            } else if (item.type === 'author') {
                                categorized.bloggers.push(item);
                            }
                        });

                        this.results = categorized;
                    } catch (error) {
                        console.error('Search error:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },
                debounceTimer: null,
                onSearchInput() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => this.performSearch(), 300);
                }
            }
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white dark:bg-gray-900">
    <!-- Navbar -->
    <nav x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-30 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <img src="{{ asset(setting('logo_url', 'uploads/logo.png')) }}" alt="{{ setting('site_name') }}" class="h-8 w-auto">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ setting('site_name', 'NextGenBeing') }}</span>
                    </a>
                </div>

                <!-- Center Navigation - Desktop -->
                <div class="hidden md:flex md:items-center md:space-x-1">
                    <!-- Articles -->
                    <a href="{{ route('posts.index') }}"
                        class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Articles</a>

                    <!-- Topics -->
                    @php
                        $navCategories = \App\Models\Category::orderBy('name')->limit(8)->get();
                    @endphp
                    <div class="relative group">
                        <button class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center">
                            Topics
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        </button>
                        <div class="absolute left-0 w-48 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <div class="bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700">
                                @foreach ($navCategories as $category)
                                    <a href="{{ route('categories.show', $category->slug) }}"
                                        class="block px-4 py-3 text-sm text-gray-900 dark:text-gray-200 hover:bg-blue-100 dark:hover:bg-gray-700 hover:text-blue-700 dark:hover:text-blue-300 first:rounded-t-lg last:rounded-b-lg transition-colors">{{ $category->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Tutorials -->
                    <a href="{{ route('tutorials.index') }}"
                        class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Tutorials</a>

                    <!-- Pricing -->
                    @if (setting('enable_subscriptions'))
                        <a href="{{ route('subscription.plans') }}"
                            class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Pricing</a>
                    @endif
                </div>

                <!-- Right Navigation -->
                <div class="flex items-center space-x-4">
                    <!-- Search Button -->
                    <div x-data="searchModal()" class="relative">
                        <button @click="isOpen = !isOpen"
                            class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>

                        <!-- Search Modal -->
                        <div x-show="isOpen" @click.outside="isOpen = false"
                            class="absolute right-0 z-50 w-96 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl dark:bg-gray-800 dark:border-gray-700">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                <input type="text" placeholder="Search..." @input="onSearchInput()"
                                    x-model="searchQuery"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="results.posts.length > 0">
                                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Posts</p>
                                        <template x-for="post in results.posts.slice(0, 5)" :key="post.id">
                                            <a :href="post.url"
                                                class="block px-3 py-2 text-sm text-gray-900 dark:text-gray-200 hover:bg-blue-100 dark:hover:bg-gray-700 rounded transition-colors"
                                                x-text="post.title"></a>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="results.tutorials.length > 0">
                                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Tutorials</p>
                                        <template x-for="tutorial in results.tutorials.slice(0, 5)" :key="tutorial.id">
                                            <a :href="tutorial.url"
                                                class="block px-3 py-2 text-sm text-gray-900 dark:text-gray-200 hover:bg-blue-100 dark:hover:bg-gray-700 rounded transition-colors"
                                                x-text="tutorial.title"></a>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="results.bloggers.length > 0">
                                    <div class="p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Bloggers</p>
                                        <template x-for="blogger in results.bloggers.slice(0, 5)" :key="blogger.id">
                                            <a :href="blogger.url"
                                                class="block px-3 py-2 text-sm text-gray-900 dark:text-gray-200 hover:bg-blue-100 dark:hover:bg-gray-700 rounded transition-colors"
                                                x-text="blogger.name"></a>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="isLoading">
                                    <div class="p-4 text-center text-gray-500">Searching...</div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Dark Mode Toggle -->
                    <button @click="toggle()"
                        class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1m-16 0H1m15.364 1.636l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>

                    <!-- User Menu / Auth -->
                    @auth
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span>{{ auth()->user()->name }}</span>
                            </button>
                            <div class="absolute right-0 w-48 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <div class="bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700">
                                    <a href="{{ route('dashboard') }}"
                                        class="block px-4 py-3 text-sm text-gray-900 dark:text-gray-200 hover:bg-blue-100 dark:hover:bg-gray-700 hover:text-blue-700 dark:hover:text-blue-300 first:rounded-t-lg transition-colors">Dashboard</a>
                                    <a href="{{ route('profile.edit') }}"
                                        class="block px-4 py-3 text-sm text-gray-900 dark:text-gray-200 hover:bg-blue-100 dark:hover:bg-gray-700 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">Settings</a>
                                    <hr class="my-1 border-gray-300 dark:border-gray-700">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full px-4 py-3 text-sm text-left text-gray-900 dark:text-gray-200 hover:bg-red-100 dark:hover:bg-gray-700 hover:text-red-700 dark:hover:text-red-300 last:rounded-b-lg transition-colors">Sign out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('login') }}"
                                class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Sign in</a>
                            <a href="{{ route('register') }}"
                                class="px-4 py-2 text-sm font-semibold text-white transition-all bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-lg">Get Started</a>
                        </div>
                    @endauth

                    <!-- Mobile menu button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 text-gray-400 md:hidden hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation - Simplified -->
            <div x-show="mobileMenuOpen" x-transition
                class="bg-white border-t border-gray-200 md:hidden dark:bg-gray-800 dark:border-gray-700"
                style="display: none;">
                <div class="pt-2 pb-3 space-y-1">
                    <!-- Articles -->
                    <a href="{{ route('posts.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-semibold text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400">Articles</a>

                    <!-- Topics Section -->
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
                        <p class="px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Topics</p>
                        @foreach ($navCategories->take(8) as $category)
                            <a href="{{ route('categories.show', $category->slug) }}"
                                class="block py-2 pl-6 pr-4 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400">{{ $category->name }}</a>
                        @endforeach
                    </div>

                    <!-- Tutorials -->
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
                        <a href="{{ route('tutorials.index') }}"
                            class="block py-2 pl-3 pr-4 text-base font-semibold text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400">Tutorials</a>
                    </div>

                    <!-- Pricing -->
                    @if (setting('enable_subscriptions'))
                        <a href="{{ route('subscription.plans') }}"
                            class="block py-2 pl-3 pr-4 text-base font-semibold text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400">Pricing</a>
                    @endif

                    <!-- Write -->
                    <a href="@auth {{ route('posts.create') }} @else {{ route('write.earn') }} @endauth"
                        class="block py-2 pl-3 pr-4 text-base font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-700 dark:hover:text-blue-300">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Write
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="min-h-screen">
        {{ $slot }}
    </main>

    <!-- Help/Report Button -->
    <div class="fixed z-40 bottom-6 right-6">
        <button @click="$dispatch('show-help-modal')"
            class="p-3 text-white transition-all bg-blue-500 rounded-full shadow focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 hover:bg-blue-500/90">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>
    </div>

    <!-- Help Contact Component -->
    @livewire('help-contact')

    <!-- Footer -->
    <footer class="border-t border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-4 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <!-- Company Info -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ setting('site_name') }}</h3>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ setting('site_description') }}
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Quick Links</h3>
                    <ul class="mt-4 space-y-2">
                        <li><a href="{{ route('posts.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">Articles</a></li>
                        <li><a href="{{ route('tutorials.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">Tutorials</a></li>
                        <li><a href="{{ route('bloggers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">Bloggers</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Support</h3>
                    <ul class="mt-4 space-y-2">
                        <li><a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">Help Center</a></li>
                        <li><a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">Contact Us</a></li>
                        <li><a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Social Links -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Follow Us</h3>
                    <div class="mt-4 flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20v-7.21H5.41V9.25h2.88V7.82c0-2.85 1.74-4.41 4.29-4.41 1.22 0 2.27.09 2.57.13v2.98h-1.76c-1.38 0-1.65.66-1.65 1.62v2.12h3.29l-.43 3.54h-2.86V20h-2.78z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2s9 5 20 5a9.5 9.5 0 00-9-5.5c4.75 2.25 7-7 7-7"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom copyright -->
            <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-center text-gray-600 dark:text-gray-400">
                    &copy; {{ date('Y') }} {{ setting('site_name') }}. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    @stack('modals')
    @livewireScripts
</body>

</html>
