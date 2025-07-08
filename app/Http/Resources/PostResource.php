<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->when($request->route()->named('api.posts.show'), $this->content),
            'featured_image' => $this->featured_image,
            'is_premium' => $this->is_premium,
            'is_featured' => $this->is_featured,
            'read_time' => $this->read_time,
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'published_at' => $this->published_at,
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'avatar' => $this->author->avatar,
                'bio' => $this->author->bio,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'color' => $this->category->color,
            ],
            'tags' => $this->tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color,
            ]),
            'comments' => $this->when(
                $request->route()->named('api.posts.show') && $this->relationLoaded('comments'),
                $this->comments->map(fn($comment) => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'likes_count' => $comment->likes_count,
                    'user' => [
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                    ],
                ])
            ),
        ];
    }
}
