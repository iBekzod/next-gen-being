<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Models\CollaborationComment;
use App\Services\CollaborationService;
use Illuminate\Support\Facades\Auth;

class CollaborationComments extends Component
{
    public Post $post;
    public array $comments = [];
    public string $newCommentContent = '';
    public ?string $selectedSection = null;
    public bool $showCommentForm = false;
    public ?int $replyingTo = null;
    public string $replyContent = '';

    protected $rules = [
        'newCommentContent' => 'required|string|min:3',
    ];

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->loadComments();

        if (!$this->canViewComments()) {
            abort(403, 'You do not have permission to view collaboration comments');
        }
    }

    public function canViewComments(): bool
    {
        $collaborationService = app(CollaborationService::class);
        return $collaborationService->canViewComments($this->post, Auth::user());
    }

    public function canAddComments(): bool
    {
        $collaborationService = app(CollaborationService::class);
        return $collaborationService->canAddComments($this->post, Auth::user());
    }

    public function loadComments()
    {
        $collaborationService = app(CollaborationService::class);
        $topLevelComments = $collaborationService->getUnresolvedComments($this->post);

        $this->comments = $topLevelComments->map(function ($comment) {
            return $this->formatComment($comment);
        })->toArray();
    }

    private function formatComment(CollaborationComment $comment): array
    {
        return [
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'user_name' => $comment->user->name,
            'user_avatar' => $comment->user->getFirstMediaUrl('avatars'),
            'content' => $comment->content,
            'section' => $comment->section,
            'status' => $comment->status,
            'created_at' => $comment->created_at->format('M d, Y H:i'),
            'is_resolved' => $comment->isResolved(),
            'can_resolve' => Auth::user()->id === $comment->user_id || $this->post->author_id === Auth::user()->id,
            'replies' => $comment->replies->map(fn($r) => $this->formatComment($r))->toArray(),
        ];
    }

    public function addComment()
    {
        $this->validate();

        try {
            if (!$this->canAddComments()) {
                $this->addError('newCommentContent', 'You do not have permission to add comments');
                return;
            }

            $collaborationService = app(CollaborationService::class);
            $collaborationService->addComment(
                $this->post,
                Auth::user(),
                $this->newCommentContent,
                $this->selectedSection
            );

            $this->reset(['newCommentContent', 'selectedSection', 'showCommentForm']);
            $this->loadComments();
            $this->dispatch('comment-added');
            $this->dispatch('notify', message: 'Comment added successfully');

        } catch (\Exception $e) {
            $this->addError('newCommentContent', $e->getMessage());
        }
    }

    public function addReply(int $commentId)
    {
        if (!$this->replyContent) {
            return;
        }

        try {
            if (!$this->canAddComments()) {
                return;
            }

            $parentComment = CollaborationComment::findOrFail($commentId);
            $collaborationService = app(CollaborationService::class);

            $collaborationService->replyToComment(
                $parentComment,
                Auth::user(),
                $this->replyContent
            );

            $this->reset(['replyContent', 'replyingTo']);
            $this->loadComments();
            $this->dispatch('notify', message: 'Reply added successfully');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error adding reply', type: 'error');
        }
    }

    public function resolveComment(int $commentId)
    {
        try {
            $comment = CollaborationComment::findOrFail($commentId);
            $collaborationService = app(CollaborationService::class);

            $collaborationService->resolveComment($comment, Auth::user());

            $this->loadComments();
            $this->dispatch('notify', message: 'Comment resolved');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error resolving comment', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.collaboration-comments');
    }
}
