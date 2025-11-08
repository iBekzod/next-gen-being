<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center space-x-2">
        <span class="text-2xl">⭐</span>
        <h3 class="font-bold text-gray-900 dark:text-white">Popular All Time</h3>
    </div>

    <!-- Posts List -->
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse ($posts as $post)
            <a href="{{ route('posts.show', $post['slug']) }}"
                class="block px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">

                <h4 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 hover:text-blue-600 dark:hover:text-blue-400">
                    {{ $post['title'] }}
                </h4>

                <div class="mt-2 flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                    <span>👁️ {{ number_format($post['views']) }}</span>
                    <span class="mx-1">•</span>
                    <span>❤️ {{ number_format($post['likes']) }}</span>
                </div>
            </a>
        @empty
            <div class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                <p>No popular posts yet</p>
            </div>
        @endforelse
    </div>
</div>
