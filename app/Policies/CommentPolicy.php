<?php
namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Comment $comment): bool
    {
        if ($user->hasAnyRole(['admin', 'content_manager'])) {
            return true;
        }

        return $user->id === $comment->user_id && $comment->created_at->addMinutes(15)->isFuture();
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($user->hasAnyRole(['admin', 'content_manager'])) {
            return true;
        }

        return $user->id === $comment->user_id;
    }

    public function approve(User $user, Comment $comment): bool
    {
        return $user->hasAnyRole(['admin', 'content_manager']);
    }

    public function reject(User $user, Comment $comment): bool
    {
        return $user->hasAnyRole(['admin', 'content_manager']);
    }
}
