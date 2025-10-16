<?php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(?User $user): bool
    {
        // Admins and user managers can view the user list
        return $user && $user->hasAnyRole(['admin', 'user_manager']);
    }

    public function view(?User $user, User $model): bool
    {
        // Admins and user managers can view any user
        // Users can view their own profile
        return $user && ($user->hasAnyRole(['admin', 'user_manager']) || $user->id === $model->id);
    }

    public function create(User $user): bool
    {
        // Only admins and user managers can create users
        return $user->hasAnyRole(['admin', 'user_manager']);
    }

    public function update(User $user, User $model): bool
    {
        // Admins can update any user
        if ($user->hasRole('admin')) {
            return true;
        }

        // User managers can update non-admin users
        if ($user->hasRole('user_manager') && !$model->hasRole('admin')) {
            return true;
        }

        // Users can update their own profile
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Can't delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Only admins can delete users
        if ($user->hasRole('admin')) {
            return true;
        }

        // User managers can delete non-admin users
        return $user->hasRole('user_manager') && !$model->hasRole('admin');
    }

    public function restore(User $user, User $model): bool
    {
        // Only admins can restore deleted users
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, User $model): bool
    {
        // Only admins can permanently delete users
        return $user->hasRole('admin');
    }
}
