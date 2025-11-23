<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\LeaderboardService;

class LeaderboardWidget extends Component
{
    use WithPagination;

    public LeaderboardService $leaderboardService;
    public $type = 'creators'; // creators, readers, engagers, trending
    public $timeRange = '30days'; // 7days, 30days, 90days, all
    public $leaderboard = [];
    public $userRank = null;
    public $isLoading = true;

    protected $queryString = ['type', 'timeRange'];

    public function mount()
    {
        $this->leaderboardService = app(LeaderboardService::class);
        $this->loadLeaderboard();
    }

    public function loadLeaderboard()
    {
        $this->isLoading = true;

        try {
            $leaderboardData = match ($this->type) {
                'creators' => $this->leaderboardService->getCreatorLeaderboard(20, $this->timeRange),
                'readers' => $this->leaderboardService->getReaderLeaderboard(20, $this->timeRange),
                'engagers' => $this->leaderboardService->getEngagerLeaderboard(20, $this->timeRange),
                'trending' => $this->leaderboardService->getTrendingPosts(20, $this->timeRange),
                default => [],
            };

            $this->leaderboard = $leaderboardData;

            // Get user's rank if authenticated
            if (auth()->check()) {
                $this->userRank = $this->leaderboardService->getUserRank(auth()->user(), $this->type, $this->timeRange);
            }
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedType()
    {
        $this->resetPage();
        $this->loadLeaderboard();
    }

    public function updatedTimeRange()
    {
        $this->resetPage();
        $this->loadLeaderboard();
    }

    public function render()
    {
        return view('livewire.leaderboard-widget');
    }
}
