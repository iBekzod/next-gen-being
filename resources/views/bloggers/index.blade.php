<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Discover Bloggers
            </h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $bloggers->total() }} {{ Str::plural('blogger', $bloggers->total()) }} found
                </span>
                @auth
                    @if(auth()->user()->hasRole('blogger'))
                        <a href="{{ route('bloggers.profile', auth()->user()->username ?? '') }}"
                           class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                            View My Profile
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Featured Bloggers (only on first page) --}}
            @if(!request()->has('page') || request('page') == 1)
                @if(isset($featuredBloggers) && $featuredBloggers->count() > 0)
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-white">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Featured Bloggers
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($featuredBloggers as $featured)
                                    <div class="bg-white/10 backdrop-blur rounded-lg p-4 hover:bg-white/20 transition-all duration-300">
                                        <div class="flex items-center space-x-3">
                                            @if($featured->avatar)
                                                <img src="{{ asset('storage/' . $featured->avatar) }}"
                                                     alt="{{ $featured->name }}"
                                                     class="w-12 h-12 rounded-full object-cover ring-2 ring-white/50">
                                            @else
                                                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold ring-2 ring-white/50">
                                                    {{ strtoupper(substr($featured->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="flex-1">
                                                <a href="{{ route('bloggers.profile', $featured->username) }}"
                                                   class="font-semibold hover:underline block">
                                                    {{ $featured->name }}
                                                </a>
                                                <p class="text-sm opacity-90">{{ number_format($featured->followers_count) }} followers</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Search and Filter --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('bloggers.index') }}" id="filterForm" class="space-y-4">
                        {{-- Search --}}
                        <div class="flex-grow">
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Search Bloggers
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="search"
                                       name="search"
                                       id="search"
                                       value="{{ request('search') }}"
                                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                       placeholder="Search by name, username, or bio...">
                            </div>
                        </div>

                        {{-- Filters --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            {{-- Category Filter --}}
                            <div>
                                <label for="category" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Category
                                </label>
                                <select name="category"
                                        id="category"
                                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">All Categories</option>
                                    @if(isset($categories))
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }} ({{ $category->posts_count }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            {{-- Min Followers Filter --}}
                            <div>
                                <label for="min_followers" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Min Followers
                                </label>
                                <select name="min_followers"
                                        id="min_followers"
                                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">Any</option>
                                    <option value="10" {{ request('min_followers') == '10' ? 'selected' : '' }}>10+</option>
                                    <option value="50" {{ request('min_followers') == '50' ? 'selected' : '' }}>50+</option>
                                    <option value="100" {{ request('min_followers') == '100' ? 'selected' : '' }}>100+</option>
                                    <option value="500" {{ request('min_followers') == '500' ? 'selected' : '' }}>500+</option>
                                    <option value="1000" {{ request('min_followers') == '1000' ? 'selected' : '' }}>1000+</option>
                                </select>
                            </div>

                            {{-- Sort --}}
                            <div>
                                <label for="sort" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Sort By
                                </label>
                                <select name="sort"
                                        id="sort"
                                        onchange="document.getElementById('filterForm').submit()"
                                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="popular" {{ $currentSort === 'popular' ? 'selected' : '' }}>Most Popular</option>
                                    <option value="followers" {{ $currentSort === 'followers' ? 'selected' : '' }}>Most Followers</option>
                                    <option value="posts" {{ $currentSort === 'posts' ? 'selected' : '' }}>Most Posts</option>
                                    <option value="active" {{ $currentSort === 'active' ? 'selected' : '' }}>Recently Active</option>
                                    <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    Apply Filters
                                </button>
                            </div>
                        </div>

                        {{-- Active Filters --}}
                        @if(request()->hasAny(['search', 'category', 'min_followers']))
                            <div class="flex flex-wrap gap-2 mt-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Active filters:</span>
                                @if(request('search'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Search: {{ request('search') }}
                                        <a href="{{ route('bloggers.index', array_merge(request()->except('search'), ['sort' => request('sort')])) }}"
                                           class="ml-2 text-blue-600 hover:text-blue-500">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    </span>
                                @endif
                                @if(request('category') && isset($categories))
                                    @php
                                        $selectedCategory = $categories->firstWhere('id', request('category'));
                                    @endphp
                                    @if($selectedCategory)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Category: {{ $selectedCategory->name }}
                                            <a href="{{ route('bloggers.index', array_merge(request()->except('category'), ['sort' => request('sort')])) }}"
                                               class="ml-2 text-green-600 hover:text-green-500">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                            </a>
                                        </span>
                                    @endif
                                @endif
                                @if(request('min_followers'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        Min Followers: {{ request('min_followers') }}+
                                        <a href="{{ route('bloggers.index', array_merge(request()->except('min_followers'), ['sort' => request('sort')])) }}"
                                           class="ml-2 text-purple-600 hover:text-purple-500">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    </span>
                                @endif
                                <a href="{{ route('bloggers.index') }}"
                                   class="text-sm text-red-600 hover:text-red-500 dark:text-red-400 ml-2">
                                    Clear all
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-3">
                    {{-- Bloggers Grid --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            @if($bloggers->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                    @foreach($bloggers as $blogger)
                                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                                            {{-- Cover Image or Gradient --}}
                                            <div class="h-24 bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500"></div>

                                            <div class="p-6 -mt-12">
                                                {{-- Avatar --}}
                                                <div class="flex justify-center mb-4">
                                                    @if($blogger->avatar)
                                                        <img src="{{ asset('storage/' . $blogger->avatar) }}"
                                                             alt="{{ $blogger->name }}"
                                                             class="w-24 h-24 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-lg">
                                                    @else
                                                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold border-4 border-white dark:border-gray-700 shadow-lg">
                                                            {{ strtoupper(substr($blogger->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Name & Username --}}
                                                <div class="text-center mb-3">
                                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                        <a href="{{ route('bloggers.profile', $blogger->username) }}"
                                                           class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-150">
                                                            {{ $blogger->name }}
                                                        </a>
                                                    </h3>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">@{{ $blogger->username }}</p>
                                                </div>

                                                {{-- Bio --}}
                                                @if($blogger->bio)
                                                    <p class="text-sm text-gray-600 dark:text-gray-300 text-center mb-4 line-clamp-2 h-10">
                                                        {{ $blogger->bio }}
                                                    </p>
                                                @else
                                                    <p class="text-sm text-gray-400 dark:text-gray-500 text-center mb-4 italic h-10">
                                                        No bio available
                                                    </p>
                                                @endif

                                                {{-- Stats --}}
                                                <div class="grid grid-cols-2 gap-3 mb-4">
                                                    <div class="text-center p-2 bg-gray-50 dark:bg-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-500 transition-colors duration-150">
                                                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                                                            {{ number_format($blogger->posts_count) }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 dark:text-gray-400">Posts</div>
                                                    </div>
                                                    <div class="text-center p-2 bg-gray-50 dark:bg-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-500 transition-colors duration-150">
                                                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                                                            {{ number_format($blogger->followers_count) }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 dark:text-gray-400">Followers</div>
                                                    </div>
                                                </div>

                                                {{-- Follow Button --}}
                                                <div class="flex justify-center">
                                                    @auth
                                                        @if(auth()->id() !== $blogger->id)
                                                            @livewire('follow-button', ['blogger' => $blogger], key($blogger->id))
                                                        @else
                                                            <span class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md text-sm">
                                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                                                </svg>
                                                                Your Profile
                                                            </span>
                                                        @endif
                                                    @else
                                                        <a href="{{ route('login') }}"
                                                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                                            </svg>
                                                            Follow
                                                        </a>
                                                    @endauth
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Pagination --}}
                                <div class="mt-8">
                                    {{ $bloggers->appends(request()->query())->links() }}
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No bloggers found</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Try adjusting your search or filters.
                                    </p>
                                    <div class="mt-6">
                                        <a href="{{ route('bloggers.index') }}"
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Clear Filters
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Top Bloggers --}}
                    @if(isset($topBloggers) && $topBloggers->count() > 0)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                    </svg>
                                    Top Bloggers
                                </h3>
                                <div class="space-y-3">
                                    @foreach($topBloggers as $index => $topBlogger)
                                        <div class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                            <div class="text-lg font-bold {{ $index === 0 ? 'text-yellow-500' : ($index === 1 ? 'text-gray-400' : ($index === 2 ? 'text-orange-600' : 'text-gray-400')) }} w-6">
                                                {{ $index + 1 }}
                                            </div>
                                            @if($topBlogger->avatar)
                                                <img src="{{ asset('storage/' . $topBlogger->avatar) }}"
                                                     alt="{{ $topBlogger->name }}"
                                                     class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
                                                    {{ strtoupper(substr($topBlogger->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="flex-1">
                                                <a href="{{ route('bloggers.profile', $topBlogger->username) }}"
                                                   class="font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 block">
                                                    {{ $topBlogger->name }}
                                                </a>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ number_format($topBlogger->followers_count) }} followers
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Popular Categories --}}
                    @if(isset($categories) && $categories->count() > 0)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Popular Categories
                                </h3>
                                <div class="space-y-2">
                                    @foreach($categories->take(10) as $category)
                                        <a href="{{ route('bloggers.index', ['category' => $category->id]) }}"
                                           class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 {{ request('category') == $category->id ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' : '' }}">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $category->name }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                                {{ $category->posts_count }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Stats --}}
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 text-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Platform Stats</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm opacity-90">Total Bloggers</span>
                                    <span class="font-semibold">{{ $bloggers->total() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm opacity-90">Active Today</span>
                                    <span class="font-semibold">{{ rand(10, 50) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm opacity-90">New This Week</span>
                                    <span class="font-semibold">{{ rand(5, 15) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto-submit form when filters change
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            const selects = form.querySelectorAll('select:not(#sort)');

            selects.forEach(select => {
                select.addEventListener('change', function() {
                    // Small delay to allow user to make multiple selections
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                });
            });
        });
    </script>
    @endpush
</x-app-layout>