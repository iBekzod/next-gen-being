<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;

class PostList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'cat')]
    public string $category = '';

    #[Url(as: 'tag')]
    public string $selectedTag = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'latest';

    #[Url(as: 'type')]
    public string $contentType = 'all';

    public bool $showFilters = false;

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'category' => ['except' => '', 'as' => 'cat'],
        'selectedTag' => ['except' => '', 'as' => 'tag'],
        'sortBy' => ['except' => 'latest', 'as' => 'sort'],
        'contentType' => ['except' => 'all', 'as' => 'type'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingSelectedTag()
    {
        $this->resetPage();
    }

    public function updatingContentType()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'category', 'selectedTag', 'sortBy', 'contentType']);
        $this->resetPage();
    }

    #[Computed]
    public function posts()
    {
        $query = Post::published()
            ->with(['author', 'category', 'tags'])
            ->withCount(['likes', 'comments' => fn($q) => $q->approved()]);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%');
            });
        }

        // Category filter
        if ($this->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $this->category));
        }

        // Tag filter
        if ($this->selectedTag) {
            $query->whereHas('tags', fn($q) => $q->where('slug', $this->selectedTag));
        }

        // Content type filter
        if ($this->contentType === 'premium') {
            $query->where('is_premium', true);
        } elseif ($this->contentType === 'free') {
            $query->where('is_premium', false);
        }

        // Sorting
        match ($this->sortBy) {
            'popular' => $query->orderByDesc('views_count')->orderByDesc('likes_count'),
            'trending' => $query->where('published_at', '>=', now()->subDays(7))
                               ->orderByDesc('views_count'),
            'oldest' => $query->orderBy('published_at'),
            default => $query->orderByDesc('published_at'),
        };

        return $query->paginate(12);
    }

    #[Computed]
    public function availableCategories()
    {
        return Category::active()
            ->withCount('publishedPosts')
            ->whereHas('publishedPosts') 
            ->orderByDesc('published_posts_count')
            ->get();
    }

    #[Computed]
    public function popularTags()
    {
        return Tag::active()
            ->popular(20)
            ->get();
    }

    #[Computed]
    public function featuredPosts()
    {
        return Post::published()
            ->featured()
            ->with(['author', 'category'])
            ->limit(3)
            ->get();
    }

    public function render()
    {
        return view('livewire.post-list');
    }
}
