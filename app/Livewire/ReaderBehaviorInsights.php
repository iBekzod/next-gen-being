<?php

namespace App\Livewire;

use Livewire\Component;

class ReaderBehaviorInsights extends Component
{
    public $postId = null;
    public $insights = [];
    public $isLoading = false;

    public function mount($postId = null)
    {
        $this->postId = $postId;
        $this->loadInsights();
    }

    public function loadInsights()
    {
        $this->isLoading = true;
        try {
            if ($this->postId) {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
                ])->get("/api/reader-tracking/{$this->postId}/analytics");
            } else {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
                ])->get('/api/v1/features/analytics/audience');
            }

            $data = $response->json() ?? [];
            $this->insights = $this->generateInsights($data);
        } finally {
            $this->isLoading = false;
        }
    }

    private function generateInsights($data)
    {
        $insights = [];

        // Engagement insight
        $bounceRate = $data['bounce_rate'] ?? 50;
        if ($bounceRate < 30) {
            $insights[] = [
                'type' => 'positive',
                'title' => 'Excellent Engagement',
                'message' => 'Your bounce rate is significantly below average. Readers are highly engaged with your content.',
            ];
        } elseif ($bounceRate > 60) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Low Engagement',
                'message' => 'High bounce rate detected. Consider improving headlines or content structure.',
            ];
        }

        // Traffic insight
        $returnRate = $data['return_rate'] ?? 0;
        if ($returnRate > 40) {
            $insights[] = [
                'type' => 'positive',
                'title' => 'Strong Audience Loyalty',
                'message' => 'Over 40% of readers return. You\'re building a loyal audience!',
            ];
        }

        // Device insight
        $mobilePercentage = $data['mobile_percentage'] ?? 0;
        if ($mobilePercentage > 70) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Mobile-First Audience',
                'message' => 'Most readers use mobile devices. Ensure your content is mobile-optimized.',
            ];
        }

        return array_slice($insights, 0, 3); // Return top 3 insights
    }

    public function render()
    {
        return view('livewire.reader-behavior-insights');
    }
}
