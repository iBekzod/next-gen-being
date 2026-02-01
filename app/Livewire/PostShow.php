<?php
namespace App\Livewire;

use App\Models\Post;
use App\Models\Comment;
use App\Services\RecommendationService;
use App\Services\Tutorial\TutorialProgressService;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostShow extends Component
{
    protected ?RecommendationService $recommendationService = null;

    public Post $post;
    public bool $showComments = true;

    #[Validate('required|min:10|max:1000')]
    public string $commentContent = '';

    public ?Comment $replyingTo = null;
    public bool $showCommentForm = false;

    protected function getRecommendationService(): RecommendationService
    {
        if (!$this->recommendationService) {
            $this->recommendationService = app(RecommendationService::class);
        }
        return $this->recommendationService;
    }

    public function mount(Post $post)
    {
        $this->post = $post;

        // Record view
        $this->post->recordView(Auth::user());

        // Track tutorial progress if user is authenticated and post is part of a series
        if (Auth::check() && $this->post->isPartOfSeries()) {
            try {
                Log::debug('Tutorial progress tracking', [
                    'user_id' => Auth::id(),
                    'post_id' => $this->post->id,
                    'series_slug' => $this->post->series_slug,
                    'series_part' => $this->post->series_part,
                ]);

                $progressService = app(TutorialProgressService::class);
                $result = $progressService->trackReading(Auth::user(), $this->post);

                Log::debug('Tutorial progress tracked', [
                    'progress_id' => $result->id,
                    'read_count' => $result->read_count,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to track tutorial progress', [
                    'error' => $e->getMessage(),
                    'user_id' => Auth::id(),
                    'post_id' => $this->post->id,
                ]);
            }
        }

        // Note: We no longer block premium content completely
        // Instead, we show a preview (handled in the view)
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

        // Force refresh to update the button state
        $this->post->refresh();
    }

    public function markTutorialComplete()
    {
        if (!Auth::check()) {
            $this->dispatch('show-auth-modal');
            return;
        }

        if (!$this->post->isPartOfSeries()) {
            return;
        }

        try {
            $progressService = app(TutorialProgressService::class);
            $progressService->markAsCompleted(Auth::user(), $this->post);

            Log::info('Tutorial marked as complete', [
                'user_id' => Auth::id(),
                'post_id' => $this->post->id,
                'series_slug' => $this->post->series_slug,
            ]);

            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'Tutorial part marked as complete!'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark tutorial as complete', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'post_id' => $this->post->id,
            ]);

            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Failed to mark tutorial as complete'
            ]);
        }
    }

    public function render()
    {
        // Fetch personalized recommendations for authenticated users
        $recommendedPosts = collect();
        if (Auth::check()) {
            $recommendedPosts = $this->getRecommendationService()->getRecommendationsForUser(Auth::user(), 3);
        }

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
            'recommendedPosts' => $recommendedPosts,
        ]);
    }
}
