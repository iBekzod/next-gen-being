<div class="space-y-6">
    <!-- Collaborators Section -->
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span>👥</span> Active Collaborators
                @if(count($collaborators) > 0)
                    <span class="ml-2 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-semibold rounded-full">
                        {{ count($collaborators) }}
                    </span>
                @endif
            </h3>
        </div>

        @if (count($collaborators) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">User</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Role</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Joined</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach ($collaborators as $collaborator)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                            <!-- User Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($collaborator['avatar'])
                                        <img src="{{ $collaborator['avatar'] }}" alt="{{ $collaborator['name'] }}" class="w-8 h-8 rounded-full">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold">
                                            {{ substr($collaborator['name'], 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $collaborator['name'] }}</p>
                                        <p class="text-gray-600 dark:text-gray-400 text-xs">{{ $collaborator['email'] }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($collaborator['role'])
                                        @case('owner') bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 @break
                                        @case('editor') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 @break
                                        @case('reviewer') bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 @break
                                        @case('viewer') bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @break
                                    @endswitch
                                ">
                                    @switch($collaborator['role'])
                                        @case('owner') 👑 Owner @break
                                        @case('editor') ✏️ Editor @break
                                        @case('reviewer') 🔍 Reviewer @break
                                        @case('viewer') 👁️ Viewer @break
                                    @endswitch
                                </span>
                            </td>

                            <!-- Joined Date -->
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $collaborator['joined_at'] }}</td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right space-x-2">
                                @unless ($collaborator['is_owner'])
                                    <button
                                        wire:click="openRoleModal({{ $collaborator['id'] }}, '{{ $collaborator['role'] }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-100 dark:hover:bg-blue-900/40 transition text-sm"
                                    >
                                        🔄 Change
                                    </button>

                                    @if ($collaborator['can_remove'])
                                        <button
                                            wire:click="removeCollaborator({{ $collaborator['id'] }})"
                                            wire:confirm="Remove {{ $collaborator['name'] }} from collaborators?"
                                            class="inline-flex items-center gap-1 px-3 py-1 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded hover:bg-red-100 dark:hover:bg-red-900/40 transition text-sm"
                                        >
                                            🗑️ Remove
                                        </button>
                                    @endif
                                @endunless
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-gray-600 dark:text-gray-400">No active collaborators yet. Start by inviting someone!</p>
            </div>
        @endif
    </div>

    <!-- Pending Invitations Section -->
    @if (count($pendingInvitations) > 0)
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span>📬</span> Pending Invitations
                <span class="ml-2 px-2 py-1 bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 text-xs font-semibold rounded-full">
                    {{ count($pendingInvitations) }}
                </span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Email</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Role</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Sent</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Expires</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                    @foreach ($pendingInvitations as $invitation)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                        <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $invitation['email'] }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @switch($invitation['role'])
                                    @case('editor') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 @break
                                    @case('reviewer') bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 @break
                                    @case('viewer') bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @break
                                @endswitch
                            ">
                                {{ ucfirst($invitation['role']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $invitation['sent_at'] }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $invitation['expires_at'] }}</td>
                        <td class="px-6 py-4 text-right">
                            <button
                                wire:click="cancelInvitation({{ $invitation['id'] }})"
                                class="inline-flex items-center gap-1 px-3 py-1 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded hover:bg-red-100 dark:hover:bg-red-900/40 transition text-sm"
                            >
                                🗑️ Cancel
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Role Change Modal -->
    @if ($showRoleModal && $selectedCollaboratorId)
    <div class="fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg max-w-sm w-full p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Role</h3>

            <select
                wire:model="newRole"
                class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white mb-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="editor">Editor</option>
                <option value="reviewer">Reviewer</option>
                <option value="viewer">Viewer</option>
            </select>

            <div class="flex gap-3 justify-end">
                <button
                    wire:click="$set('showRoleModal', false)"
                    class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition"
                >
                    Cancel
                </button>
                <button
                    wire:click="updateRole"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
                >
                    Update Role
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
