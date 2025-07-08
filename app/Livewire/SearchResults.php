<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Laravel\Scout\Builder;

class SearchResults extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: true)]
    public string $query = '';

    #[Url(as: 'type')]
    public string $searchType = 'posts';

    public int $postsCount = 0;
    public int $authorsCount = 0;

    public function mount()
    {
        if ($this->query) {
            $this->updateCounts();
        }
    }

    public function updatedQuery()
    {
        $this->resetPage();
        $this->updateCounts();
    }

    public function updatedSearchType()
    {
        $this->resetPage();
    }

    private function updateCounts()
    {
        if (empty($this->query)) {
            $this->postsCount = 0;
            $this->authorsCount = 0;
            return;
        }

        $this->postsCount = Post::search($this->query)
            ->where('status', 'published')
            ->count();

        $this->authorsCount = User::where('name', 'like', "%{$this->query}%")
            ->orWhere('bio', 'like', "%{$this->query}%")
            ->count();
    }

    public function getPostsProperty()
    {
        if (empty($this->query)) {
            return collect();
        }

        return Post::search($this->query)
            ->where('status', 'published')
            ->paginate(10, 'page', 'posts-page');
    }

    public function getAuthorsProperty()
    {
        if (empty($this->query)) {
            return collect();
        }

        return User::where('name', 'like', "%{$this->query}%")
            ->orWhere('bio', 'like', "%{$this->query}%")
            ->withCount('posts')
            ->paginate(10, 'page', 'authors-page');
    }

    public function render()
    {
        return view('livewire.search-results', [
            'posts' => $this->searchType === 'posts' ? $this->posts : collect(),
            'authors' => $this->searchType === 'authors' ? $this->authors : collect(),
        ]);
    }
}
