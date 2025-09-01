<?php
namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Post $post): bool
    {
        if (!$post->isPublished()) {
            return $user && ($user->id === $post->author_id || $user->hasAnyRole(['admin', 'content_manager']));
        }

        if ($post->is_premium) {
            return $user && $user->isPremium();
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'content_manager', 'blogger']);
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->hasAnyRole(['admin', 'content_manager'])) {
            return true;
        }

        return $user->id === $post->author_id;
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('content_manager')) {
            return true;
        }

        return $user->id === $post->author_id;
    }

    public function publish(User $user, Post $post): bool
    {
        if ($user->hasAnyRole(['admin', 'content_manager'])) {
            return true;
        }

        return $user->id === $post->author_id && $user->hasRole('blogger');
    }

    public function feature(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['admin', 'content_manager']);
    }
}
