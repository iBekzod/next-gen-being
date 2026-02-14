<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\StreakService;
use Illuminate\Support\Collection;

class StreakWidget extends Component
{
    public StreakService $streakService;
    public Collection $streaks;
    public ?array $readingStreak = null;
    public ?array $writingStreak = null;
    public bool $isLoading = true;

    public function mount()
    {
        $this->streakService = app(StreakService::class);
        $this->loadStreaks();
    }

    public function loadStreaks()
    {
        $this->isLoading = true;

        try {
            $user = auth()->user();
            $this->streaks = $user->streaks ?? collect();

            // Get current streaks
            $this->readingStreak = $this->streakService->getCurrentStreak($user, 'reading');
            $this->writingStreak = $this->streakService->getCurrentStreak($user, 'writing');
        } finally {
            $this->isLoading = false;
        }
    }

    public function recordActivity($type)
    {
        $user = auth()->user();

        try {
            $result = $this->streakService->recordActivity($user, $type);

            if ($result['success']) {
                $this->dispatch('streakUpdated', [
                    'type' => $type,
                    'streak' => $result['streak'],
                ]);
                $this->loadStreaks();
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to record activity');
        }
    }

    public function render()
    {
        return view('livewire.streak-widget');
    }
}
