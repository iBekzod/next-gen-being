<div class="space-y-6">
    @if($isLoading)
        <div class="text-center text-gray-500">Loading preferences...</div>
    @else
        <form wire:submit="savePreferences" class="space-y-6">
            <!-- Categories -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold mb-4">📚 Preferred Categories</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach(['Technology', 'Science', 'Business', 'Art', 'Sport', 'Health'] as $category)
                        <label class="flex items-center gap-2 p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer">
                            <input 
                                type="checkbox" 
                                wire:model="selectedCategories" 
                                value="{{ $category }}"
                                class="w-4 h-4"
                            >
                            <span>{{ $category }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Content Types -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold mb-4">📖 Content Type Preferences</h3>
                <div class="space-y-3">
                    @foreach(['article' => 'Articles', 'tutorial' => 'Tutorials', 'story' => 'Stories', 'news' => 'News'] as $type => $label)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <span>{{ $label }}</span>
                            <input 
                                type="range" 
                                min="0" 
                                max="100"
                                wire:model="contentTypeScores.{{ $type }}"
                                class="w-24"
                            >
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button 
                    type="submit"
                    class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                >
                    ✓ Save Preferences
                </button>
                <button 
                    type="button"
                    wire:click="resetPreferences"
                    class="px-6 py-2 bg-gray-300 rounded hover:bg-gray-400"
                >
                    Reset to Default
                </button>
            </div>
        </form>
    @endif
</div>
