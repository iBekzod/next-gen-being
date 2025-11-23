<div class="space-y-6">
    <form wire:submit="analyzeSEO" class="space-y-4">
        <!-- Title Input -->
        <div class="bg-white rounded-lg shadow-md p-4">
            <label class="block text-sm font-medium mb-2">📝 Title (60 chars max)</label>
            <input 
                type="text" 
                wire:model="title" 
                maxlength="60"
                placeholder="Enter your post title"
                class="w-full border rounded px-3 py-2"
                @if($isAnalyzing) disabled @endif
            >
            <p class="text-xs text-gray-600 mt-1">{{ strlen($title) }}/60 characters</p>
            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Meta Description -->
        <div class="bg-white rounded-lg shadow-md p-4">
            <label class="block text-sm font-medium mb-2">🔍 Meta Description (160 chars max)</label>
            <textarea 
                wire:model="description" 
                maxlength="160"
                rows="2"
                placeholder="Enter your meta description"
                class="w-full border rounded px-3 py-2"
                @if($isAnalyzing) disabled @endif
            ></textarea>
            <p class="text-xs text-gray-600 mt-1">{{ strlen($description) }}/160 characters</p>
            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Content -->
        <div class="bg-white rounded-lg shadow-md p-4">
            <label class="block text-sm font-medium mb-2">📄 Content (minimum 100 chars)</label>
            <textarea 
                wire:model="content" 
                rows="6"
                placeholder="Enter your post content"
                class="w-full border rounded px-3 py-2"
                @if($isAnalyzing) disabled @endif
            ></textarea>
            <p class="text-xs text-gray-600 mt-1">{{ strlen($content) }} characters</p>
            @error('content') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Submit Button -->
        <button 
            type="submit"
            @if($isAnalyzing) disabled @endif
            class="w-full px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
        >
            @if($isAnalyzing) Analyzing... @else 🔎 Analyze SEO @endif
        </button>
    </form>

    <!-- SEO Results -->
    @if($seoAnalysis)
        <div class="bg-white rounded-lg shadow-md p-6 space-y-4">
            <h3 class="text-lg font-bold">📊 SEO Analysis Results</h3>

            <!-- Score Summary -->
            <div class="grid grid-cols-4 gap-4">
                <div class="text-center p-4 bg-green-50 rounded">
                    <p class="text-3xl font-bold text-green-600">{{ $seoAnalysis['score'] ?? 0 }}/100</p>
                    <p class="text-sm text-gray-600">Overall Score</p>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded">
                    <p class="text-3xl font-bold text-blue-600">{{ count($seoAnalysis['positive'] ?? []) }}</p>
                    <p class="text-sm text-gray-600">Positives</p>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded">
                    <p class="text-3xl font-bold text-yellow-600">{{ count($seoAnalysis['warnings'] ?? []) }}</p>
                    <p class="text-sm text-gray-600">Warnings</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded">
                    <p class="text-3xl font-bold text-red-600">{{ count($seoAnalysis['errors'] ?? []) }}</p>
                    <p class="text-sm text-gray-600">Errors</p>
                </div>
            </div>

            <!-- Recommendations -->
            @if($recommendations)
                <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                    <h4 class="font-semibold mb-2">💡 Recommendations</h4>
                    <ul class="space-y-2">
                        @foreach($recommendations as $rec)
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 mt-1">→</span>
                                <span class="text-sm">{{ $rec }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Reset Button -->
        <button 
            type="button"
            wire:click="resetAnalysis"
            class="w-full px-6 py-2 bg-gray-300 rounded hover:bg-gray-400"
        >
            Clear Results
        </button>
    @endif
</div>
