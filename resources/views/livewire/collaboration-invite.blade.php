<div class="space-y-4">
    <!-- Success Message -->
    @if ($inviteSuccess)
    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-green-600 dark:text-green-400 text-lg">✓</span>
            <p class="text-green-800 dark:text-green-200 font-medium">{{ $successMessage }}</p>
        </div>
        <button wire:click="dismissSuccess" class="text-green-600 dark:text-green-400 hover:text-green-700">✕</button>
    </div>
    @endif

    <!-- Toggle Button -->
    <button
        wire:click="toggleForm"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
    >
        <span>📧</span>
        <span>{{ $showForm ? 'Cancel' : 'Invite Collaborator' }}</span>
    </button>

    <!-- Invitation Form -->
    @if ($showForm)
    <div class="p-6 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Invite Someone to Collaborate</h3>

        <form wire:submit="sendInvitation" class="space-y-4">
            <!-- Email Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                <input
                    type="email"
                    wire:model="email"
                    placeholder="collaborator@example.com"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                @error('email') <span class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                <select
                    wire:model="role"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="editor">Editor - Can edit and review</option>
                    <option value="reviewer">Reviewer - Can only review content</option>
                    <option value="viewer">Viewer - Read-only access</option>
                </select>
            </div>

            <!-- Role Description -->
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm text-blue-800 dark:text-blue-200">
                <strong>{{ ucfirst($role) }}:</strong>
                @switch($role)
                    @case('editor')
                        Can edit post content, view comments, and collaborate
                        @break
                    @case('reviewer')
                        Can view content, add editorial comments, and suggest changes
                        @break
                    @case('viewer')
                        Can view content and version history only
                        @break
                @endswitch
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-lg font-medium transition"
            >
                <span wire:loading.remove>Send Invitation</span>
                <span wire:loading>Sending...</span>
            </button>
        </form>
    </div>
    @endif
</div>
