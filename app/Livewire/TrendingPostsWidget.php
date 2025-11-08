<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TrendingService;

class TrendingPostsWidget extends Component
{
    public string $period = '7days';
    public array $posts = [];
    public array $periods = [
        '24hours' => '24 Hours',
        '7days' => '7 Days',
        '30days' => '30 Days',
    ];

    public function mount(): void
    {
        $this->loadTrending();
    }

    public function loadTrending(): void
    {
        $service = app(TrendingService::class);
        $trendingPosts = $service->getTrendingPosts(10, $this->period);

        $this->posts = $trendingPosts->map(fn ($post) => [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'author_name' => $post->author->name,
            'author_slug' => $post->author->username ?? $post->author->id,
            'category_name' => $post->category->name,
            'category_slug' => $post->category->slug,
            'views' => $post->views_count ?? 0,
            'likes' => $post->likes_count ?? 0,
            'comments' => $post->comments_count ?? 0,
            'image' => $post->featured_image,
            'excerpt' => $post->excerpt,
            'created_at' => $post->created_at->format('M d, Y'),
        ])->toArray();
    }

    public function setPeriod(string $period): void
    {
        if (array_key_exists($period, $this->periods)) {
            $this->period = $period;
            $this->loadTrending();
        }
    }

    public function render()
    {
        return view('livewire.trending-posts-widget');
    }
}
