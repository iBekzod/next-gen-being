<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\UserInteraction;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class UserDashboard extends Component
{
    use WithPagination;

    public string $activeTab = 'overview';
    public string $postsFilter = 'all';

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function setPostsFilter(string $filter)
    {
        $this->postsFilter = $filter;
        $this->resetPage();
    }

    public function deletePost(Post $post)
    {
        if ($post->author_id !== Auth::id()) {
            abort(403);
        }

        $post->delete();
        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Post deleted successfully!'
        ]);
    }

    public function getUserPostsProperty()
    {
        $query = Auth::user()->posts()->with(['category', 'tags']);

        match ($this->postsFilter) {
            'published' => $query->where('status', 'published'),
            'draft' => $query->where('status', 'draft'),
            'scheduled' => $query->where('status', 'scheduled'),
            default => $query,
        };

        return $query->latest()->paginate(10);
    }

    public function getBookmarkedPostsProperty()
    {
        return Post::whereHas('interactions', function ($query) {
                $query->where('user_id', Auth::id())
                      ->where('type', 'bookmark');
            })
            ->with(['author', 'category'])
            ->latest()
            ->paginate(10);
    }

    public function getStatsProperty()
    {
        $user = Auth::user();

        return [
            'total_posts' => $user->posts()->count(),
            'published_posts' => $user->posts()->where('status', 'published')->count(),
            'total_views' => $user->posts()->sum('views_count'),
            'total_likes' => $user->posts()->sum('likes_count'),
            'total_comments' => $user->posts()->sum('comments_count'),
            'bookmarks' => UserInteraction::where('user_id', $user->id)
                                         ->where('type', 'bookmark')
                                         ->count(),
        ];
    }

    public function render()
    {
        return view('livewire.user-dashboard', [
            'stats' => $this->stats,
            'userPosts' => $this->activeTab === 'posts' ? $this->userPosts : collect(),
            'bookmarkedPosts' => $this->activeTab === 'bookmarks' ? $this->bookmarkedPosts : collect(),
        ]);
    }
}
