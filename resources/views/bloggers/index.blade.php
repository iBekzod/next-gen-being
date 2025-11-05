<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Discover Bloggers
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Search and Filter --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('bloggers.index') }}" class="flex flex-col md:flex-row gap-4">
                        {{-- Search --}}
                        <div class="flex-grow">
                            <label for="search" class="sr-only">Search bloggers</label>
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
                                       placeholder="Search by name or username...">
                            </div>
                        </div>

                        {{-- Sort --}}
                        <div class="flex gap-2">
                            <select name="sort"
                                    onchange="this.form.submit()"
                                    class="block w-full md:w-auto pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="popular" {{ $currentSort === 'popular' ? 'selected' : '' }}>Most Popular</option>
                                <option value="followers" {{ $currentSort === 'followers' ? 'selected' : '' }}>Most Followers</option>
                                <option value="posts" {{ $currentSort === 'posts' ? 'selected' : '' }}>Most Posts</option>
                                <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest</option>
                            </select>

                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Bloggers Grid --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($bloggers->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($bloggers as $blogger)
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                    <div class="p-6">
                                        {{-- Avatar --}}
                                        <div class="flex justify-center mb-4">
                                            @if($blogger->avatar)
                                                <img src="{{ asset('storage/' . $blogger->avatar) }}"
                                                     alt="{{ $blogger->name }}"
                                                     class="w-24 h-24 rounded-full object-cover border-4 border-gray-200 dark:border-gray-600">
                                            @else
                                                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold border-4 border-gray-200 dark:border-gray-600">
                                                    {{ strtoupper(substr($blogger->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Name & Username --}}
                                        <div class="text-center mb-3">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                <a href="{{ route('bloggers.profile', $blogger->username) }}"
                                                   class="hover:text-blue-600 dark:hover:text-blue-400">
                                                    {{ $blogger->name }}
                                                </a>
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">@{{ $blogger->username }}</p>
                                        </div>

                                        {{-- Bio --}}
                                        @if($blogger->bio)
                                            <p class="text-sm text-gray-600 dark:text-gray-300 text-center mb-4 line-clamp-2">
                                                {{ $blogger->bio }}
                                            </p>
                                        @endif

                                        {{-- Stats --}}
                                        <div class="grid grid-cols-2 gap-3 mb-4">
                                            <div class="text-center p-2 bg-gray-50 dark:bg-gray-600 rounded">
                                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                                    {{ number_format($blogger->posts_count) }}
                                                </div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400">Posts</div>
                                            </div>
                                            <div class="text-center p-2 bg-gray-50 dark:bg-gray-600 rounded">
                                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                                    {{ number_format($blogger->followers_count) }}
                                                </div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400">Followers</div>
                                            </div>
                                        </div>

                                        {{-- Follow Button --}}
                                        <div class="flex justify-center">
                                            @livewire('follow-button', ['blogger' => $blogger], key($blogger->id))
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-6">
                            {{ $bloggers->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No bloggers found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Try adjusting your search or filters.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
