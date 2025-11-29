<?php

namespace App\Livewire;

use Livewire\Component;

class SocialShareAnalytics extends Component
{
    public $postId = null;
    public $shareData = [];
    public $isLoading = false;
    public $timeRange = '30days';

    public function mount($postId = null)
    {
        $this->postId = $postId;
        if ($this->postId) {
            $this->loadShareData();
        }
    }

    public function updatedTimeRange()
    {
        if ($this->postId) {
            $this->loadShareData();
        }
    }

    public function loadShareData()
    {
        if (!$this->postId) {
            return;
        }

        $this->isLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get("/api/social-shares/{$this->postId}/breakdown", [
                'period' => $this->timeRange,
            ]);

            $this->shareData = $response->json() ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.social-share-analytics');
    }
}
