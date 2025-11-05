<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FollowButton extends Component
{
    public User $blogger;
    public bool $isFollowing = false;
    public int $followerCount = 0;

    public function mount(User $blogger)
    {
        $this->blogger = $blogger;
        $this->updateFollowState();
    }

    public function toggleFollow()
    {
        if (!Auth::check()) {
            $this->dispatch('show-login-prompt');
            return;
        }

        $user = Auth::user();

        if ($user->id === $this->blogger->id) {
            // Can't follow yourself
            return;
        }

        if ($this->isFollowing) {
            $user->unfollow($this->blogger);
        } else {
            $user->follow($this->blogger);
        }

        $this->updateFollowState();

        // Dispatch event for other components to update
        $this->dispatch('follow-toggled', bloggerId: $this->blogger->id);
    }

    protected function updateFollowState()
    {
        if (Auth::check()) {
            $this->isFollowing = Auth::user()->isFollowing($this->blogger);
        } else {
            $this->isFollowing = false;
        }

        $this->followerCount = $this->blogger->followers()->count();
    }

    public function render()
    {
        return view('livewire.follow-button');
    }
}
