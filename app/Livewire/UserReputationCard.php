<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class UserReputationCard extends Component
{
    public User $user;
    public int $reputationPoints = 0;
    public string $level = 'beginner';
    public int $levelProgress = 0;
    public array $badges = [];
    public array $achievements = [];
    public bool $showDetails = false;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->loadReputation();
    }

    public function loadReputation(): void
    {
        $reputation = $this->user->getOrCreateReputation();
        $this->reputationPoints = $reputation->points;
        $this->level = $reputation->level;
        $this->levelProgress = $reputation->level_progress;

        // Load badges
        $this->badges = $this->user->badges()
            ->orderByPivot('earned_at', 'desc')
            ->get()
            ->toArray();

        // Load achievements
        $this->achievements = $this->user->achievements()
            ->orderBy('achieved_at', 'desc')
            ->get()
            ->toArray();
    }

    public function toggleDetails(): void
    {
        $this->showDetails = !$this->showDetails;
    }

    public function getLevelColor(): string
    {
        return match($this->level) {
            'beginner' => 'gray',
            'intermediate' => 'blue',
            'advanced' => 'purple',
            'expert' => 'orange',
            'legend' => 'red',
            default => 'gray',
        };
    }

    public function getLevelIcon(): string
    {
        return match($this->level) {
            'beginner' => '🌱',
            'intermediate' => '📈',
            'advanced' => '🚀',
            'expert' => '⚡',
            'legend' => '👑',
            default => '❓',
        };
    }

    public function render()
    {
        return view('livewire.user-reputation-card');
    }
}
