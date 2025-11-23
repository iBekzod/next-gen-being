<div class="p-6 bg-white dark:bg-slate-800 rounded-lg shadow-lg">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">🤖 AI Tutorial Generator</h2>
        <p class="text-gray-600 dark:text-gray-400">Generate comprehensive, production-grade tutorial series automatically</p>
    </div>

    <!-- Success Message -->
    @if($successMessage)
    <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 text-green-700 dark:text-green-400 rounded-lg">
        <strong>✓ Success:</strong> {{ $successMessage }}
    </div>
    @endif

    <!-- Error Message -->
    @if($error)
    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 text-red-700 dark:text-red-400 rounded-lg">
        <strong>✕ Error:</strong> {{ $error }}
    </div>
    @endif

    <!-- Main Form -->
    <form wire:submit="generateTutorial" class="space-y-6">
        <!-- Topic Input -->
        <div>
            <label for="topic" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tutorial Topic <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                wire:model="topic"
                placeholder="e.g., Building a Marketplace with Laravel, Advanced Docker & Kubernetes"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            @error('topic')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Be specific about what you want to teach. Example: "Building E-Commerce Platform with Laravel 12"
            </p>
        </div>

        <!-- Parts Selection -->
        <div>
            <label for="parts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Number of Parts <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-3 gap-3">
                @foreach([3, 5, 8] as $partOption)
                <label class="flex items-center cursor-pointer">
                    <input
                        type="radio"
                        wire:model="parts"
                        value="{{ $partOption }}"
                        class="w-4 h-4 text-blue-600"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        {{ $partOption }} Parts
                    </span>
                </label>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                <strong>3 parts:</strong> Quick intro (30 min) |
                <strong>5 parts:</strong> Standard (1.5 hrs) |
                <strong>8 parts:</strong> Comprehensive (3 hrs)
            </p>
        </div>

        <!-- Publish Option -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    wire:model="publish"
                    class="w-4 h-4 text-blue-600 rounded"
                />
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    <strong>Auto-publish</strong> tutorials (otherwise save as drafts)
                </span>
            </label>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                ⚠️ Drafts should be reviewed before publishing
            </p>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition disabled:opacity-50"
            >
                <span wire:loading.remove>▶ Generate Tutorial</span>
                <span wire:loading>⏳ Generating...</span>
            </button>

            <button
                type="button"
                wire:click="previewGeneration"
                wire:loading.attr="disabled"
                class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg font-medium transition disabled:opacity-50"
            >
                👁️ Preview First Part
            </button>
        </div>
    </form>

    <!-- Divider -->
    <hr class="my-8 border-gray-300 dark:border-gray-600" />

    <!-- Quick Templates -->
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">⚡ Quick Templates</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Click to generate pre-configured tutorials:</p>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            <button
                wire:click="quickGenerate('marketplace')"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg text-sm font-medium hover:opacity-90 transition disabled:opacity-50"
            >
                🛒 Marketplace
            </button>
            <button
                wire:click="quickGenerate('ecommerce')"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-lg text-sm font-medium hover:opacity-90 transition disabled:opacity-50"
            >
                💳 E-Commerce
            </button>
            <button
                wire:click="quickGenerate('api')"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-gradient-to-r from-green-500 to-teal-500 text-white rounded-lg text-sm font-medium hover:opacity-90 transition disabled:opacity-50"
            >
                🔌 RESTful APIs
            </button>
            <button
                wire:click="quickGenerate('mobile')"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg text-sm font-medium hover:opacity-90 transition disabled:opacity-50"
            >
                📱 Mobile Backend
            </button>
            <button
                wire:click="quickGenerate('devops')"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg text-sm font-medium hover:opacity-90 transition disabled:opacity-50"
            >
                🐳 Docker & K8s
            </button>
            <button
                wire:click="quickGenerate('testing')"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg text-sm font-medium hover:opacity-90 transition disabled:opacity-50"
            >
                ✅ Testing
            </button>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="mt-8 grid md:grid-cols-3 gap-4">
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <h4 class="font-semibold text-blue-900 dark:text-blue-200 mb-2">⏱️ Duration</h4>
            <p class="text-sm text-blue-800 dark:text-blue-300">Generation takes 5-15 minutes depending on complexity</p>
        </div>

        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
            <h4 class="font-semibold text-green-900 dark:text-green-200 mb-2">✨ Quality</h4>
            <p class="text-sm text-green-800 dark:text-green-300">Production-grade code with best practices included</p>
        </div>

        <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
            <h4 class="font-semibold text-purple-900 dark:text-purple-200 mb-2">🔔 Notifications</h4>
            <p class="text-sm text-purple-800 dark:text-purple-300">You'll get notified when generation is complete</p>
        </div>
    </div>
</div>
