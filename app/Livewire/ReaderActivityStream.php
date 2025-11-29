<?php

namespace App\Livewire;

use Livewire\Component;

class ReaderActivityStream extends Component
{
    public $postId = null;
    public $activities = [];
    public $isLoading = false;
    public $pollInterval = 5000; // 5 seconds

    public function mount($postId = null)
    {
        $this->postId = $postId;
        $this->loadActivities();
    }

    public function loadActivities()
    {
        $this->isLoading = true;
        try {
            if ($this->postId) {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
                ])->get("/api/reader-tracking/{$this->postId}/activity");
            } else {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
                ])->get('/api/v1/features/analytics/performance');
            }

            $this->activities = $response->json('activities') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.reader-activity-stream');
    }
}
