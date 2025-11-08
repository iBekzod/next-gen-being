<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Models\PostCollaborator;
use App\Services\CollaborationService;
use Illuminate\Support\Facades\Auth;

class CollaborationManager extends Component
{
    public Post $post;
    public array $collaborators = [];
    public array $pendingInvitations = [];
    public string $selectedAction = '';
    public ?int $selectedCollaboratorId = null;
    public string $newRole = '';
    public bool $showRoleModal = false;

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->loadCollaborators();
        $this->loadPendingInvitations();

        if (!$this->canManage()) {
            abort(403, 'You do not have permission to manage collaborators');
        }
    }

    public function canManage(): bool
    {
        $collaborationService = app(CollaborationService::class);
        return $collaborationService->canManageCollaborators($this->post, Auth::user());
    }

    public function loadCollaborators()
    {
        $this->collaborators = $this->post->activeCollaborators()
            ->with('user')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'user_id' => $c->user_id,
                'name' => $c->user->name,
                'email' => $c->user->email,
                'avatar' => $c->user->getFirstMediaUrl('avatars'),
                'role' => $c->role,
                'joined_at' => $c->joined_at->format('M d, Y'),
                'is_owner' => $c->isOwner(),
                'can_remove' => !$c->isOwner(),
            ])
            ->toArray();
    }

    public function loadPendingInvitations()
    {
        $this->pendingInvitations = $this->post->collaboratorInvitations()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'email' => $i->email,
                'role' => $i->role,
                'sent_at' => $i->created_at->format('M d, Y'),
                'expires_at' => $i->expires_at->format('M d, Y'),
            ])
            ->toArray();
    }

    public function openRoleModal(int $collaboratorId, string $currentRole)
    {
        $this->selectedCollaboratorId = $collaboratorId;
        $this->newRole = $currentRole;
        $this->showRoleModal = true;
    }

    public function updateRole()
    {
        if (!$this->selectedCollaboratorId || !$this->newRole) {
            return;
        }

        try {
            $collaborator = PostCollaborator::findOrFail($this->selectedCollaboratorId);
            $collaborationService = app(CollaborationService::class);

            $collaborationService->updateCollaboratorRole(
                $this->post,
                $collaborator->user,
                $this->newRole,
                Auth::user()
            );

            $this->loadCollaborators();
            $this->showRoleModal = false;
            $this->dispatch('collaborator-updated');
            $this->dispatch('notify', message: 'Collaborator role updated');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error updating role: ' . $e->getMessage(), type: 'error');
        }
    }

    public function removeCollaborator(int $collaboratorId)
    {
        try {
            $collaborator = PostCollaborator::findOrFail($collaboratorId);
            $collaborationService = app(CollaborationService::class);

            $collaborationService->removeCollaborator(
                $this->post,
                $collaborator->user,
                Auth::user()
            );

            $this->loadCollaborators();
            $this->dispatch('collaborator-removed');
            $this->dispatch('notify', message: 'Collaborator removed');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error removing collaborator', type: 'error');
        }
    }

    public function cancelInvitation(int $invitationId)
    {
        try {
            $invitation = \App\Models\CollaborationInvitation::findOrFail($invitationId);
            $invitation->cancel();

            $this->loadPendingInvitations();
            $this->dispatch('notify', message: 'Invitation cancelled');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error cancelling invitation', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.collaboration-manager');
    }
}
