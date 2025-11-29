<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Browse Tutorials</h2>
        <p class="text-gray-600 mt-1">Learn from our comprehensive tutorial series</p>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-sm p-4 space-y-4">
        <input
            type="text"
            wire:model.debounce.500ms="searchQuery"
            placeholder="Search tutorials..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />

        <select
            wire:model="filterCategory"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
            <option value="all">All Categories</option>
            <option value="web">Web Development</option>
            <option value="mobile">Mobile Development</option>
            <option value="devops">DevOps</option>
            <option value="ai">AI & Machine Learning</option>
            <option value="database">Databases</option>
        </select>
    </div>

    <!-- Loading State -->
    @if($isLoading)
    <div class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>
    @else
        <!-- Tutorials Grid -->
        @if(count($tutorials) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tutorials as $tutorial)
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-6 border border-gray-200">
                <div class="mb-4">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $tutorial['category'] ?? 'Tutorial' }}
                    </span>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $tutorial['title'] ?? 'Tutorial' }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ $tutorial['description'] ?? 'Learn this comprehensive tutorial' }}</p>

                <div class="space-y-2 mb-4 text-sm text-gray-600">
                    <p>📚 {{ $tutorial['lesson_count'] ?? 0 }} lessons</p>
                    <p>⏱️ {{ $tutorial['duration'] ?? 'Unknown' }} hours</p>
                    <p>👤 By {{ $tutorial['author'] ?? 'Admin' }}</p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-1">
                        <span class="text-yellow-400">★</span>
                        <span class="text-sm font-medium text-gray-900">{{ $tutorial['rating'] ?? 'N/A' }}</span>
                    </div>
                    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium transition">
                        Start Learning
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm">
            <p class="text-gray-600">No tutorials found</p>
            <p class="text-sm text-gray-500 mt-2">Try adjusting your search or filters</p>
        </div>
        @endif
    @endif
</div>
