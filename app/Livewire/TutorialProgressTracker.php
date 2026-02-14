<?php

namespace App\Livewire;

use Livewire\Component;

class TutorialProgressTracker extends Component
{
    public ?int $tutorialId = null;
    public array $progress = [];
    public bool $isLoading = false;

    public function mount($tutorialId = null)
    {
        $this->tutorialId = $tutorialId;
        $this->loadProgress();
    }

    public function loadProgress()
    {
        if (!$this->tutorialId) {
            return;
        }

        $this->isLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get("/api/tutorials/{$this->tutorialId}/progress");

            $this->progress = $response->json() ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function completeLesson($lessonId)
    {
        \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
        ])->post("/api/tutorials/{$this->tutorialId}/lessons/{$lessonId}/complete");

        $this->loadProgress();
    }

    public function render()
    {
        return view('livewire.tutorial-progress-tracker');
    }
}
