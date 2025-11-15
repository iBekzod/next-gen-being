<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Models\User;
use App\Services\RecommendationService;

class PostRecommendations extends Component
{
    public Post $currentPost;
    public User $user;
    public array $recommendations = [];
    public string $type = 'similar'; // similar, personalized, trending, followed

    protected $recommendationService;

    public function mount(Post $post, ?User $user = null, string $type = 'similar')
    {
        $this->recommendationService = app(RecommendationService::class);
        $this->currentPost = $post;
        $this->user = $user ?? auth()->user();
        $this->type = $type;

        $this->loadRecommendations();
    }

    public function loadRecommendations()
    {
        $limit = 4;

        $posts = match ($this->type) {
            'similar' => $this->recommendationService->getSimilarPosts($this->currentPost, $limit),
            'personalized' => $this->user ? $this->recommendationService->getRecommendationsForUser($this->user, $limit) : collect(),
            'trending' => $this->recommendationService->getTrendingPosts($limit),
            'followed' => $this->user ? $this->recommendationService->getFollowedAuthorPosts($this->user, $limit) : collect(),
            default => collect(),
        };

        $this->recommendations = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'views' => $post->views_count ?? 0,
                'likes' => $post->likes_count ?? 0,
                'comments' => $post->comments()->count(),
                'featured_image' => $post->featured_image,
                'category_name' => $post->category->name,
                'category_slug' => $post->category->slug,
                'author_name' => $post->author->name,
                'author_slug' => $post->author->username ?? $post->author->slug ?? 'user-' . $post->author->id,
                'published_at' => $post->published_at->format('M d, Y'),
            ];
        })->toArray();

        if ($this->user && count($this->recommendations) > 0) {
            foreach ($this->recommendations as $rec) {
                $post = Post::find($rec['id']);
                $this->recommendationService->logRecommendationShown($this->user, $post, $this->type);
            }
        }
    }

    public function trackClick($postId)
    {
        if ($this->user) {
            $post = Post::find($postId);
            if ($post) {
                $this->recommendationService->trackRecommendationClick($this->user, $post, $this->type);
            }
        }
    }

    public function render()
    {
        return view('livewire.post-recommendations');
    }
}
