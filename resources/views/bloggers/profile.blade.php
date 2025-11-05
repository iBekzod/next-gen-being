<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $blogger->name }}'s Profile
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Blogger Profile Header --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        {{-- Avatar --}}
                        <div class="flex-shrink-0">
                            @if($blogger->avatar)
                                <img src="{{ asset('storage/' . $blogger->avatar) }}"
                                     alt="{{ $blogger->name }}"
                                     class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700">
                            @else
                                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-4xl font-bold border-4 border-gray-200 dark:border-gray-700">
                                    {{ strtoupper(substr($blogger->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        {{-- Blogger Info --}}
                        <div class="flex-grow">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-4">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">
                                        {{ $blogger->name }}
                                    </h1>
                                    <p class="text-gray-600 dark:text-gray-400">@username}}</p>
                                </div>

                                {{-- Follow Button --}}
                                <div class="mt-4 md:mt-0">
                                    @livewire('follow-button', ['blogger' => $blogger])
                                </div>
                            </div>

                            {{-- Bio --}}
                            @if($blogger->bio)
                                <p class="text-gray-700 dark:text-gray-300 mb-4">{{ $blogger->bio }}</p>
                            @endif

                            {{-- Stats --}}
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ number_format($stats['total_posts']) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Posts</div>
                                </div>
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ number_format($stats['total_followers']) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Followers</div>
                                </div>
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ number_format($stats['total_following']) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Following</div>
                                </div>
                                @if($stats['premium_posts'] > 0)
                                    <div class="text-center p-3 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900 dark:to-yellow-800 rounded-lg">
                                        <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">
                                            {{ number_format($stats['premium_posts']) }}
                                        </div>
                                        <div class="text-sm text-yellow-700 dark:text-yellow-300">Premium</div>
                                    </div>
                                @endif
                            </div>

                            {{-- Social Links --}}
                            @if($blogger->social_links)
                                <div class="flex gap-3">
                                    @if(isset($blogger->social_links['twitter']))
                                        <a href="{{ $blogger->social_links['twitter'] }}"
                                           target="_blank"
                                           class="text-gray-600 hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-400">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if(isset($blogger->social_links['github']))
                                        <a href="{{ $blogger->social_links['github'] }}"
                                           target="_blank"
                                           class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if(isset($blogger->social_links['linkedin']))
                                        <a href="{{ $blogger->social_links['linkedin'] }}"
                                           target="_blank"
                                           class="text-gray-600 hover:text-blue-700 dark:text-gray-400 dark:hover:text-blue-500">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if(isset($blogger->social_links['website']))
                                        <a href="{{ $blogger->social_links['website'] }}"
                                           target="_blank"
                                           class="text-gray-600 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Blogger's Posts --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        Published Posts
                    </h2>

                    @if($posts->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($posts as $post)
                                <article class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                    @if($post->featured_image)
                                        <img src="{{ asset('storage/' . $post->featured_image) }}"
                                             alt="{{ $post->title }}"
                                             class="w-full h-48 object-cover">
                                    @endif

                                    <div class="p-5">
                                        {{-- Premium Badge --}}
                                        @if($post->is_premium)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 mb-2">
                                                Premium
                                            </span>
                                        @endif

                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                            <a href="{{ route('posts.show', $post->slug) }}"
                                               class="hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $post->title }}
                                            </a>
                                        </h3>

                                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-3 line-clamp-3">
                                            {{ $post->excerpt }}
                                        </p>

                                        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                {{ number_format($post->views_count) }}
                                            </span>
                                            <span>{{ $post->published_at?->diffForHumans() }}</span>
                                        </div>

                                        {{-- Tags --}}
                                        @if($post->tags->count() > 0)
                                            <div class="flex flex-wrap gap-2 mt-3">
                                                @foreach($post->tags->take(3) as $tag)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-6">
                            {{ $posts->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No posts yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                This blogger hasn't published any posts yet.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
