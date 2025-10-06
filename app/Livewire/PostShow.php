<?php
namespace App\Livewire;

use App\Models\Post;
use App\Models\Comment;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class PostShow extends Component
{
    public Post $post;
    public bool $showComments = true;

    #[Validate('required|min:10|max:1000')]
    public string $commentContent = '';

    public ?Comment $replyingTo = null;
    public bool $showCommentForm = false;

    public function mount(Post $post)
    {
        $this->post = $post;

        // Record view
        $this->post->recordView(Auth::user());

        // Check if user can view premium content
        if ($this->post->is_premium && !Auth::user()?->isPremium()) {
            abort(403, 'This is premium content. Please subscribe to access.');
        }
    }

    public function toggleLike()
    {
        if (!Auth::check()) {
            $this->dispatch('show-auth-modal');
            return;
        }

        $user = Auth::user();
        $interaction = $user->interactions()
            ->where('interactable_type', Post::class)
            ->where('interactable_id', $this->post->id)
            ->where('type', 'like')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $this->post->decrement('likes_count');
        } else {
            $user->interactions()->create([
                'interactable_type' => Post::class,
                'interactable_id' => $this->post->id,
                'type' => 'like',
            ]);
            $this->post->increment('likes_count');
        }

        $this->post->refresh();
    }

    public function toggleBookmark()
    {
        if (!Auth::check()) {
            $this->dispatch('show-auth-modal');
            return;
        }

        $user = Auth::user();
        $interaction = $user->interactions()
            ->where('interactable_type', Post::class)
            ->where('interactable_id', $this->post->id)
            ->where('type', 'bookmark')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $this->post->decrement('bookmarks_count');
        } else {
            $user->interactions()->create([
                'interactable_type' => Post::class,
                'interactable_id' => $this->post->id,
                'type' => 'bookmark',
            ]);
            $this->post->increment('bookmarks_count');
        }

        $this->post->refresh();
    }

    public function submitComment()
    {
        if (!Auth::check()) {
            $this->dispatch('show-auth-modal');
            return;
        }

        $this->validate();

        $comment = Comment::create([
            'content' => $this->commentContent,
            'post_id' => $this->post->id,
            'user_id' => Auth::id(),
            'parent_id' => $this->replyingTo?->id,
            'status' => 'pending', // Auto-approve for trusted users
        ]);

        // Auto-approve for trusted users
        if (Auth::user()->hasAnyRole(['admin', 'content_manager'])) {
            $comment->approve();
        }

        $this->reset(['commentContent', 'replyingTo', 'showCommentForm']);
        $this->dispatch('comment-added');

        session()->flash('message', 'Comment submitted successfully!');
    }

    public function replyTo(Comment $comment)
    {
        $this->replyingTo = $comment;
        $this->showCommentForm = true;
    }

    public function cancelReply()
    {
        $this->reset(['replyingTo', 'showCommentForm']);
    }

    public function toggleCommentLike($commentId)
    {
        if (!Auth::check()) {
            $this->dispatch('show-auth-modal');
            return;
        }

        $comment = Comment::findOrFail($commentId);
        $user = Auth::user();

        $interaction = $user->interactions()
            ->where('interactable_type', Comment::class)
            ->where('interactable_id', $comment->id)
            ->where('type', 'like')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $comment->decrement('likes_count');
        } else {
            $user->interactions()->create([
                'interactable_type' => Comment::class,
                'interactable_id' => $comment->id,
                'type' => 'like',
            ]);
            $comment->increment('likes_count');
        }
    }

    public function toggleFollow()
    {
        if (!Auth::check()) {
            $this->dispatch('show-auth-modal');
            return;
        }

        $user = Auth::user();
        $author = $this->post->author;

        if ($user->isFollowing($author)) {
            $user->unfollow($author);
            $this->dispatch('show-notification', [
                'type' => 'info',
                'message' => 'Unfollowed ' . $author->name
            ]);
        } else {
            $user->follow($author);
            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'Now following ' . $author->name
            ]);
        }
    }

    public function render()
    {
        return view('livewire.post-show', [
            'comments' => $this->post->comments()
                ->approved()
                ->topLevel()
                ->with(['user', 'approvedReplies.user'])
                ->latest()
                ->get(),
            'relatedPosts' => Post::published()
                ->where('id', '!=', $this->post->id)
                ->where('category_id', $this->post->category_id)
                ->limit(3)
                ->get(),
        ]);
    }
}
