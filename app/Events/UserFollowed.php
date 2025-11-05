<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserFollowed
{
    use Dispatchable, SerializesModels;

    public User $follower;
    public User $followedUser;

    /**
     * Create a new event instance.
     */
    public function __construct(User $follower, User $followedUser)
    {
        $this->follower = $follower;
        $this->followedUser = $followedUser;
    }
}
