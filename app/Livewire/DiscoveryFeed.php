<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\PersonalizedFeedService;

class DiscoveryFeed extends Component
{
    use WithPagination;

    public PersonalizedFeedService $feedService;
    public string $feedType = 'discovery'; // discovery, home, reading_list, personalized, trending, followed
    public array $posts = [];
    public bool $isLoading = true;

    protected $queryString = ['feedType'];

    public function mount()
    {
        $this->feedService = app(PersonalizedFeedService::class);
        $this->loadFeed();
    }

    public function loadFeed()
    {
        $this->isLoading = true;

        try {
            $feed = $this->feedService->getFeed(
                user: auth()->user(),
                type: $this->feedType,
                limit: 20,
                offset: ($this->getPage() - 1) * 20
            );

            $this->posts = $feed['posts'] ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedFeedType()
    {
        $this->resetPage();
        $this->loadFeed();
    }

    public function likePost($postId)
    {
        // Dispatch to API
        $this->dispatch('postLiked', postId: $postId);
    }

    public function bookmarkPost($postId)
    {
        // Dispatch to API
        $this->dispatch('postBookmarked', postId: $postId);
    }

    public function render()
    {
        return view('livewire.discovery-feed');
    }
}
