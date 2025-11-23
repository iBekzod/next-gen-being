<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Challenge;
use App\Services\ChallengeService;
use Illuminate\Support\Facades\Log;

class ChallengeCard extends Component
{
    public Challenge $challenge;
    public ChallengeService $challengeService;
    public $isJoined = false;
    public $userProgress = 0;
    public $isProcessing = false;

    public function mount(Challenge $challenge)
    {
        $this->challenge = $challenge;
        $this->challengeService = app(ChallengeService::class);

        if (auth()->check()) {
            $participation = $challenge->participants()
                ->where('user_id', auth()->id())
                ->first();

            $this->isJoined = $participation !== null;
            $this->userProgress = $participation?->progress ?? 0;
        }
    }

    public function joinChallenge()
    {
        if (!auth()->check()) {
            $this->dispatch('requireLogin');
            return;
        }

        $this->isProcessing = true;

        try {
            $result = $this->challengeService->joinChallenge(auth()->user(), $this->challenge);

            if ($result['success']) {
                $this->isJoined = true;
                $this->dispatch('challengeJoined', [
                    'challenge' => $this->challenge->title,
                ]);
            } else {
                $this->dispatch('error', $result['message'] ?? 'Failed to join challenge');
            }
        } catch (\Exception $e) {
            Log::error('Challenge join error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'challenge_id' => $this->challenge->id,
            ]);
            $this->dispatch('error', 'An error occurred');
        } finally {
            $this->isProcessing = false;
        }
    }

    public function render()
    {
        return view('livewire.challenge-card');
    }
}
