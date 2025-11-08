<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Services\CollaborationService;
use Illuminate\Support\Facades\Auth;

class CollaborationInvite extends Component
{
    public Post $post;
    public string $email = '';
    public string $role = 'editor';
    public array $errors = [];
    public bool $showForm = false;
    public bool $inviteSuccess = false;
    public string $successMessage = '';

    protected $rules = [
        'email' => 'required|email',
        'role' => 'required|in:editor,reviewer,viewer',
    ];

    public function mount(Post $post)
    {
        $this->post = $post;

        // Only allow author and managers to invite
        if (!$this->canInvite()) {
            abort(403, 'You do not have permission to invite collaborators');
        }
    }

    public function canInvite(): bool
    {
        $collaborationService = app(CollaborationService::class);
        return $collaborationService->canManageCollaborators($this->post, Auth::user());
    }

    public function sendInvitation()
    {
        $this->validate();

        try {
            $collaborationService = app(CollaborationService::class);
            $invitation = $collaborationService->inviteCollaborator(
                $this->post,
                Auth::user(),
                $this->email,
                $this->role
            );

            $this->inviteSuccess = true;
            $this->successMessage = "{$this->email} has been invited as a {$this->role}";
            $this->reset(['email', 'role']);
            $this->showForm = false;

            // Emit event to refresh collaborators list
            $this->dispatch('collaborator-invited');

        } catch (\Exception $e) {
            $this->addError('email', $e->getMessage());
        }
    }

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        $this->reset(['errors', 'inviteSuccess']);
    }

    public function dismissSuccess()
    {
        $this->inviteSuccess = false;
    }

    public function render()
    {
        return view('livewire.collaboration-invite');
    }
}
