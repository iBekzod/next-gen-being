<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeSwitcher()" x-init="init()"
    x-effect="document.documentElement.classList.toggle('dark', darkMode)">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', setting('site_name', 'NextGenBeing'))</title>
    <meta name="description" content="@yield('description', setting('site_description'))">

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
                        const data = await response.json();

                        // Parse results into sections
                        this.results = {
                            posts: (data.posts || []).slice(0, 3),
                            tutorials: (data.tutorials || []).slice(0, 3),
                            bloggers: (data.bloggers || []).slice(0, 3)
                        };
                    } catch (error) {
                        console.error('Search error:', error);
                        this.results = { posts: [], tutorials: [], bloggers: [] };
                    } finally {
                        this.isLoading = false;
                    }
                },
                open() {
                    this.isOpen = true;
                    this.$nextTick(() => {
                        this.$refs.searchField?.focus();
                    });
                },
                close() {
                    this.isOpen = false;
                    this.searchQuery = '';
                    this.results = { posts: [], tutorials: [], bloggers: [] };
                }
            }
        };
    </script>
    <!-- Scripts -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Additional Head Content -->
    @stack('head')

    @php
        $siteName = setting('site_name', config('app.name'));
        $siteUrl = rtrim(config('app.url', url('/')), '/');
        $pageTitle = trim(strip_tags($__env->yieldContent('title', $siteName)));
        $pageTitle = $pageTitle !== '' ? $pageTitle : $siteName;
        $pageDescription = trim(strip_tags($__env->yieldContent('description', setting('site_description', 'Insights for ambitious builders.'))));
        $pageKeywords = trim($__env->yieldContent('keywords', setting('default_meta_keywords', 'NextGenBeing, AI workflows, startup playbooks')));
        $pageAuthor = trim($__env->yieldContent('author', setting('company_name', $siteName)));
        $pageType = trim($__env->yieldContent('og_type', 'website'));
        $robots = trim($__env->yieldContent('robots', 'index, follow'));
        $shareImage = $__env->yieldContent('share_image', setting('default_meta_image', setting('site_logo', asset('uploads/logo.png'))));
        $canonical = $__env->yieldContent('canonical', url()->current());
        $locale = str_replace('_', '-', app()->getLocale());
        $ogLocale = str_replace('-', '_', $locale);
        $twitterHandle = ltrim((string) setting('social_twitter_handle'), '@');
        $socialLinks = setting('social_links', []);
        $sameAs = [];

        if (is_array($socialLinks)) {
            foreach ($socialLinks as $link) {
                if (! empty($link)) {
                    $sameAs[] = $link;
                }
            }
        }

        $ensureAbsoluteUrl = function (?string $value) {
            if (blank($value)) {
                return $value;
            }

            if (\Illuminate\Support\Str::startsWith($value, ['http://', 'https://'])) {
                return $value;
            }

            return url($value);
        };

        $shareImage = $ensureAbsoluteUrl($shareImage);
        $canonical = $ensureAbsoluteUrl($canonical);
        $logoPath = setting('site_logo', asset('uploads/logo.png'));
        $logoUrl = $ensureAbsoluteUrl($logoPath);
        $domain = parse_url($canonical, PHP_URL_HOST) ?? parse_url($siteUrl, PHP_URL_HOST);
        $twitterUsername = $twitterHandle !== '' ? '@' . $twitterHandle : null;

        $rssUrl = \Illuminate\Support\Facades\Route::has('feed.rss') ? route('feed.rss') : null;
        $sitemapUrl = \Illuminate\Support\Facades\Route::has('seo.sitemap') ? route('seo.sitemap') : null;
        $supportEmail = setting('support_email', 'support@' . ($domain ?? parse_url($siteUrl, PHP_URL_HOST)));

        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => $siteUrl,
            'logo' => $logoUrl,
        ];

        if (! empty($sameAs)) {
            $organizationSchema['sameAs'] = $sameAs;
        }

        if (! empty($supportEmail)) {
            $organizationSchema['contactPoint'] = [[
                '@type' => 'ContactPoint',
                'email' => $supportEmail,
                'contactType' => 'customer support',
            ]];
        }

        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('search', ['q' => '{search_term_string}']),
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="title" content="{{ $pageTitle }}">
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <meta name="author" content="{{ $pageAuthor }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">
    @if ($rssUrl)
        <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} RSS Feed" href="{{ $rssUrl }}">
    @endif
    @if ($sitemapUrl)
        <link rel="sitemap" type="application/xml" href="{{ $sitemapUrl }}">
    @endif
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $pageType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:image:alt" content="{{ $pageTitle }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
    @if (! empty($domain))
        <meta name="twitter:domain" content="{{ $domain }}">
    @endif
    @if ($twitterUsername)
        <meta name="twitter:site" content="{{ $twitterUsername }}">
        <meta name="twitter:creator" content="{{ $twitterUsername }}">
    @endif

    @if (!empty($organizationSchema['logo']))
        <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
    @endif
    <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>

    @stack('structured-data')
</head>

    <body class="font-sans antialiased">
        @php
            $navCategories = collect();
            if (
                class_exists(\App\Models\Category::class) &&
                \Illuminate\Support\Facades\Schema::hasTable('categories')
            ) {
                $navCategories = \App\Models\Category::active()->ordered()->get();
            }
        @endphp
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 bg-white border-b border-gray-200 dark:bg-gray-900 dark:border-gray-700 backdrop-blur-sm bg-white/95 dark:bg-gray-900/95"
            x-data="{ mobileMenuOpen: false }">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and Main Navigation -->
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center flex-shrink-0">
                            <img class="w-auto h-8" src="{{ asset('uploads/logo.png') }}" alt="{{ setting('site_name') }}">
                            <span
                                class="ml-2 text-xl font-bold text-gray-900 dark:text-white">{{ setting('site_name') }}</span>
                        </a>

                        <!-- Desktop Navigation - Main Menu -->
                        <div class="hidden md:ml-8 md:flex md:space-x-1">
                            <!-- Articles -->
                            <a href="{{ route('posts.index') }}"
                                class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-700 transition-colors border-b-2 border-transparent dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-600">
                                Articles
                            </a>

                            <!-- Topics -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-700 transition-colors border-b-2 border-transparent dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-600">
                                    Topics
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition
                                    class="absolute left-0 z-50 w-56 py-2 mt-1 bg-white rounded-lg shadow-xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                    <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Browse Topics</p>
                                    </div>
                                    @foreach ($navCategories as $category)
                                        <a href="{{ route('categories.show', $category->slug) }}"
                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                            {{ $category->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Tutorials -->
                            <a href="{{ route('tutorials.index') }}"
                                class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-700 transition-colors border-b-2 border-transparent dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-600">
                                Tutorials
                            </a>

                            <!-- Bloggers (Conditional) -->
                            @if ((isset($totalBloggers) && $totalBloggers > 5) || \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['blogger', 'content_creator']))->count() > 5)
                                <a href="{{ route('bloggers.index') }}"
                                    class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-700 transition-colors border-b-2 border-transparent dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-600">
                                    Bloggers
                                </a>
                            @endif

                            <!-- Write -->
                            <a href="@auth {{ route('posts.create') }} @else {{ route('write.earn') }} @endauth"
                                class="inline-flex items-center px-3 py-2 text-sm font-semibold text-blue-600 transition-colors border-b-2 border-transparent dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:border-blue-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Write
                            </a>

                            <!-- Pricing -->
                            @if (setting('enable_subscriptions'))
                                <a href="{{ route('subscription.plans') }}"
                                    class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-700 transition-colors border-b-2 border-transparent dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-600">
                                    Pricing
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Search and User Menu -->
                    <div class="flex items-center space-x-4">
                        <!-- Search - Enhanced -->
                        <div class="relative hidden md:block" x-data="searchModal()">
                            <button @click="open" class="p-2 text-gray-500 transition-colors hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400" title="Search articles">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>

                            <template x-teleport="body">
                                <div x-cloak x-show="isOpen" x-transition.opacity.duration.200ms
                                    @keydown.escape.window="close" @click.self="close"
                                    class="fixed inset-0 z-[60] flex items-start justify-center px-4 pt-20 pb-12 overflow-y-auto bg-black/60 backdrop-blur-sm">
                                    <div class="w-full max-w-2xl overflow-hidden bg-white rounded-xl shadow-2xl dark:bg-gray-900"
                                        x-transition.scale.origin-top @click.outside="close">
                                        <!-- Search Input -->
                                        <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 p-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                                <input x-ref="searchField"
                                                    type="text"
                                                    placeholder="Search articles, tutorials, bloggers..."
                                                    x-model="searchQuery"
                                                    @input.debounce="performSearch()"
                                                    class="flex-1 text-lg text-gray-900 placeholder-gray-500 bg-transparent border-0 dark:text-white focus:ring-0"
                                                    @keydown.escape.stop="close">
                                                <button x-show="isLoading" class="ml-2">
                                                    <svg class="w-5 h-5 text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Results -->
                                        <div class="max-h-[60vh] overflow-y-auto">
                                            <!-- Posts Section -->
                                            <div x-show="results.posts.length > 0" class="border-b border-gray-200 dark:border-gray-700">
                                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800">
                                                    <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Articles</h3>
                                                </div>
                                                <template x-for="post in results.posts">
                                                    <a :href="`/posts/${post.slug}`" @click="close()" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors border-b border-gray-100 dark:border-gray-800 last:border-0">
                                                        <img x-show="post.featured_image" :src="post.featured_image" :alt="post.title" class="w-12 h-12 rounded object-cover flex-shrink-0">
                                                        <div class="flex-1 min-w-0">
                                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1" x-text="post.title"></h4>
                                                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-1" x-text="post.excerpt"></p>
                                                        </div>
                                                    </a>
                                                </template>
                                            </div>

                                            <!-- Tutorials Section -->
                                            <div x-show="results.tutorials.length > 0" class="border-b border-gray-200 dark:border-gray-700">
                                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800">
                                                    <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Tutorials</h3>
                                                </div>
                                                <template x-for="tutorial in results.tutorials">
                                                    <a :href="`/posts/${tutorial.slug}`" @click="close()" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors border-b border-gray-100 dark:border-gray-800 last:border-0">
                                                        <svg class="w-4 h-4 mt-1 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.669 0-3.218.51-4.5 1.385A7.968 7.968 0 009 4.804z"></path>
                                                        </svg>
                                                        <div class="flex-1 min-w-0">
                                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1" x-text="tutorial.title"></h4>
                                                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-1" x-text="tutorial.excerpt"></p>
                                                        </div>
                                                    </a>
                                                </template>
                                            </div>

                                            <!-- Bloggers Section -->
                                            <div x-show="results.bloggers.length > 0">
                                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800">
                                                    <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Bloggers</h3>
                                                </div>
                                                <div class="grid grid-cols-3 gap-3 p-4">
                                                    <template x-for="blogger in results.bloggers">
                                                        <a :href="`/blogger/${blogger.username}`" @click="close()" class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                            <img :src="blogger.avatar" :alt="blogger.name" class="w-12 h-12 rounded-full object-cover">
                                                            <div class="text-center">
                                                                <p class="text-xs font-semibold text-gray-900 dark:text-white line-clamp-1" x-text="blogger.name"></p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1" x-text="`@${blogger.username}`"></p>
                                                            </div>
                                                        </a>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- No Results Message -->
                                            <div x-show="searchQuery && !isLoading && results.posts.length === 0 && results.tutorials.length === 0 && results.bloggers.length === 0"
                                                class="p-8 text-center">
                                                <p class="text-gray-600 dark:text-gray-400">No results found for "<span x-text="searchQuery"></span>"</p>
                                            </div>

                                            <!-- Show More Button -->
                                            <div x-show="searchQuery && !isLoading && (results.posts.length > 0 || results.tutorials.length > 0 || results.bloggers.length > 0)"
                                                class="border-t border-gray-200 dark:border-gray-700 p-4">
                                                <a :href="`{{ route('search') }}?q=${encodeURIComponent(searchQuery)}`" @click="close()"
                                                    class="block w-full px-4 py-2 text-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-800 rounded-lg transition-colors">
                                                    View all results for "<span x-text="searchQuery"></span>"
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button @click="toggle()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <svg x-show="!darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                                </path>
                            </svg>
                            <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </button>

                        <!-- User Menu -->
                        @auth
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="flex items-center space-x-2 text-sm bg-white rounded-full dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <img class="w-8 h-8 rounded-full"
                                        src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                                        alt="{{ auth()->user()->name }}">
                                    <span
                                        class="hidden text-gray-700 md:block dark:text-gray-300">{{ auth()->user()->name }}</span>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-transition
                                    class="absolute right-0 z-50 w-48 py-1 mt-2 bg-white rounded-md shadow-lg dark:bg-gray-800">
                                    <a href="{{ route('dashboard') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Dashboard</a>
                                    @if (auth()->user()->hasAnyRole(['admin', 'content_manager', 'blogger']))
                                        <a href="{{ route('posts.create') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Write
                                            Article</a>
                                    @endif
                                    @if (auth()->user()->hasAnyRole(['admin', 'content_manager']))
                                        <a href="/admin"
                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Admin
                                            Panel</a>
                                    @endif
                                    <a href="{{ route('dashboard.settings') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Settings</a>
                                    <hr class="my-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full px-4 py-2 text-sm text-left text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Sign
                                            out</button>
                                    </form>
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

                    <!-- Bloggers (Conditional) -->
                    @if ((isset($totalBloggers) && $totalBloggers > 5) || \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['blogger', 'content_creator']))->count() > 5)
                        <a href="{{ route('bloggers.index') }}"
                            class="block py-2 pl-3 pr-4 text-base font-semibold text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400">Bloggers</a>
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

                    <!-- Pricing -->
                    @if (setting('enable_subscriptions'))
                        <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
                            <a href="{{ route('subscription.plans') }}"
                                class="block py-2 pl-3 pr-4 text-base font-semibold text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400">Pricing</a>
                        </div>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="min-h-screen">
            @yield('content')
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
                    <!-- Newsletter Widget -->
                    <div class="col-span-1 md:col-span-2 mb-8">
                        @livewire('newsletter-subscribe')
                    </div>

                    <!-- Company Info -->
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center mb-4">
                            <img class="w-auto h-8" src="{{ setting('site_logo', '/uploads/logo.png') }}"
                                alt="{{ setting('site_name') }}">
                            <span
                                class="ml-2 text-xl font-bold text-gray-900 dark:text-white">{{ setting('site_name') }}</span>
                        </div>
                        <p class="max-w-md mb-4 text-gray-600 dark:text-gray-400">
                            {{ setting('site_description') }}
                        </p>
                        @php $socialLinks = setting('social_links', []); @endphp
                        @if ($socialLinks)
                            <div class="flex space-x-4">
                                @if (isset($socialLinks['twitter']))
                                    <a href="{{ $socialLinks['twitter'] }}"
                                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                        </svg>
                                    </a>
                                @endif
                                @if (isset($socialLinks['linkedin']))
                                    <a href="{{ $socialLinks['linkedin'] }}"
                                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                        </svg>
                                    </a>
                                @endif
                                @if (isset($socialLinks['github']))
                                    <a href="{{ $socialLinks['github'] }}"
                                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                                        </svg>
                                    </a>
                                @endif
                                @if (isset($socialLinks['youtube']))
                                    <a href="{{ $socialLinks['youtube'] }}"
                                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold tracking-wider text-gray-900 uppercase dark:text-white">
                            Content</h3>
                        <ul class="space-y-3">
                            <li><a href="{{ route('posts.index') }}"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">All
                                    Articles</a></li>
                            <li><a href="{{ route('tutorials.index') }}"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Tutorial
                                    Series</a></li>
                            @foreach ($navCategories->take(3) as $category)
                                <li><a href="{{ route('categories.show', $category->slug) }}"
                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">{{ $category->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Support -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold tracking-wider text-gray-900 uppercase dark:text-white">
                            Support</h3>
                        <ul class="space-y-3">
                            <li><button @click="$dispatch('show-help-modal')"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Help
                                    Center</button></li>
                            @if (setting('enable_subscriptions'))
                                <li><a href="{{ route('subscription.plans') }}"
                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Pricing</a>
                                </li>
                            @endif
                            @auth
                                <li><a href="{{ route('subscription.manage') }}"
                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Manage
                                        Subscription</a></li>
                            @endauth
                            <li><a href="/privacy"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Privacy
                                    Policy</a></li>
                            <li><a href="/refund-policy"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Refund
                                    Policy</a></li>
                            <li><a href="/terms"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Terms &amp; Conditions</a></li>
                        </ul>
                    </div>
                </div>

                <div class="pt-8 mt-8 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-center text-gray-400">
                        &copy; {{ date('Y') }} {{ setting('site_name') }}. All rights reserved. Built with Laravel &
                        Filament.
                    </p>
                </div>
            </div>
        </footer>

        <!-- Notification Component -->
        <div id="notification-container" class="fixed z-50 space-y-2 top-4 right-4"></div>

        @livewireScripts(["assetUrl" => asset("vendor/livewire/livewire.min.js")])

        <!-- Analytics -->
        @if (setting('enable_analytics') && setting('google_analytics_id'))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_id') }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());
                gtag('config', '{{ setting('google_analytics_id') }}');
            </script>
        @endif

        <!-- Additional Scripts -->
        @stack('scripts')

        <script>
            // Notification system
            document.addEventListener('livewire:init', () => {
                Livewire.on('show-notification', (event) => {
                    showNotification(event.message, event.type || 'info');
                });
            });

            function showNotification(message, type = 'info') {
                const container = document.getElementById('notification-container');
                const notification = document.createElement('div');

                const colors = {
                    success: 'bg-green-500',
                    error: 'bg-red-500',
                    warning: 'bg-yellow-500',
                    info: 'bg-blue-500'
                };

                notification.className =
                    `${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full opacity-0`;
                notification.innerHTML = `
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium">${message}</p>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

                container.appendChild(notification);

                setTimeout(() => {
                    notification.classList.remove('translate-x-full', 'opacity-0');
                }, 100);

                setTimeout(() => {
                    notification.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            }

            // Theme switcher and search modal functions are now defined in the <head> section

            window.addEventListener('unhandledrejection', (event) => {
                if (event.reason?.isFromCancelledTransition) {
                    event.preventDefault();
                }
            });

            // Auto-refresh CSRF token to prevent 419 errors
            setInterval(function() {
                fetch('/sanctum/csrf-cookie', {
                    method: 'GET',
                    credentials: 'same-origin'
                }).then(() => {
                    // Update CSRF token in meta tag
                    const token = document.querySelector('meta[name="csrf-token"]');
                    if (token && document.cookie.match(/XSRF-TOKEN=([^;]+)/)) {
                        const newToken = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)[1]);
                        if (newToken && token.getAttribute('content') !== newToken) {
                            console.log('CSRF token refreshed');
                        }
                    }
                });
            }, 60000); // Refresh every 60 seconds
        </script>
    </body>

    </html>






