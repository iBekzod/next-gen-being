<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span class="text-2xl">🔥</span>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Trending Now</h2>
        </div>

        <!-- Period Selector -->
        <div class="flex space-x-2">
            @foreach ($periods as $periodKey => $periodLabel)
                <button wire:click="setPeriod('{{ $periodKey }}')"
                    class="px-3 py-1 text-xs font-medium rounded-full transition {{ $period === $periodKey ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    {{ $periodLabel }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Posts List -->
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse ($posts as $index => $post)
            <a href="{{ route('posts.show', $post['slug']) }}"
                class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">

                <div class="flex items-start space-x-4">
                    <!-- Ranking Number -->
                    <div class="flex-shrink-0 text-right">
                        <span class="text-3xl font-bold {{ $index === 0 ? 'text-yellow-400' : ($index === 1 ? 'text-gray-400' : ($index === 2 ? 'text-orange-400' : 'text-gray-300')) }}">
                            #{{ $index + 1 }}
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Category Badge -->
                        <a href="{{ route('categories.show', $post['category_slug']) }}"
                            class="inline-block text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-1">
                            {{ $post['category_name'] }}
                        </a>

                        <!-- Title -->
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 hover:text-blue-600 dark:hover:text-blue-400 transition">
                            {{ $post['title'] }}
                        </h3>

                        <!-- Meta Info -->
                        <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                            <a href="{{ route('bloggers.profile', $post['author_slug']) }}"
                                class="hover:text-gray-700 dark:hover:text-gray-300">
                                By {{ $post['author_name'] }}
                            </a>
                            <span>{{ $post['created_at'] }}</span>
                        </div>

                        <!-- Engagement Stats -->
                        <div class="mt-3 flex items-center space-x-4 text-xs">
                            <span class="text-gray-600 dark:text-gray-400">
                                👁️ {{ number_format($post['views']) }}
                            </span>
                            <span class="text-gray-600 dark:text-gray-400">
                                ❤️ {{ number_format($post['likes']) }}
                            </span>
                            <span class="text-gray-600 dark:text-gray-400">
                                💬 {{ number_format($post['comments']) }}
                            </span>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    @if ($post['image'])
                        <img src="{{ asset('storage/' . $post['image']) }}"
                            alt="{{ $post['title'] }}"
                            class="w-20 h-20 object-cover rounded flex-shrink-0">
                    @else
                        <div class="w-20 h-20 rounded bg-gradient-to-br from-blue-400 to-blue-600 flex-shrink-0 flex items-center justify-center">
                            <span class="text-2xl">📝</span>
                        </div>
                    @endif
                </div>
            </a>
        @empty
            <div class="px-6 py-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">No trending posts yet</p>
            </div>
        @endforelse
    </div>

    <!-- View All Link -->
    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 text-center border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('posts.index') }}"
            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
            View all posts →
        </a>
    </div>
</div>
