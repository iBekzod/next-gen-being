<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">Your Progress</h2>

    @if($isLoading)
    <div class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>
    @else
        @if(isset($progress['overall_progress']))
        <!-- Overall Progress -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Overall Progress</span>
                <span class="text-sm font-bold text-gray-900">{{ $progress['overall_progress'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all" style="width: {{ $progress['overall_progress'] }}%"></div>
            </div>
        </div>

        <!-- Lessons -->
        <div class="space-y-2">
            @foreach($progress['lessons'] ?? [] as $lesson)
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
                <div class="flex-shrink-0">
                    @if($lesson['completed'])
                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        ✓
                    </div>
                    @else
                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-gray-600">
                        {{ $loop->index + 1 }}
                    </div>
                    @endif
                </div>

                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $lesson['title'] ?? 'Lesson' }}</p>
                    <p class="text-xs text-gray-500">{{ $lesson['duration'] ?? '0' }} minutes</p>
                </div>

                @if(!$lesson['completed'])
                <button
                    wire:click="completeLesson({{ $lesson['id'] }})"
                    class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium transition"
                >
                    Start
                </button>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <p>No progress data available</p>
        </div>
        @endif
    @endif
</div>
