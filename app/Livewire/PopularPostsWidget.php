<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TrendingService;

class PopularPostsWidget extends Component
{
    public array $posts = [];

    public function mount(): void
    {
        $this->loadPopular();
    }

    public function loadPopular(): void
    {
        $service = app(TrendingService::class);
        $popularPosts = $service->getPopularPosts(5);

        $this->posts = $popularPosts->map(fn ($post) => [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'views' => $post->views_count ?? 0,
            'likes' => $post->likes_count ?? 0,
            'author_name' => $post->author->name,
        ])->toArray();
    }

    public function render()
    {
        return view('livewire.popular-posts-widget');
    }
}
