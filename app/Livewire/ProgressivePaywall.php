<?php

namespace App\Livewire;

use App\Models\Post;
use App\Services\ContentMeteringService;
use App\Services\ContentAccessService;
use Livewire\Component;

class ProgressivePaywall extends Component
{
    public Post $post;
    public $remainingFreeArticles;
    public $paywallType;
    public $tierDisplayName;
    public $tierPrice;
    public $showPaywall = false;

    protected ContentMeteringService $meteringService;
    protected ContentAccessService $accessService;

    public function boot(
        ContentMeteringService $meteringService,
        ContentAccessService $accessService
    ) {
        $this->meteringService = $meteringService;
        $this->accessService = $accessService;
    }

    public function mount(Post $post)
    {
        $this->post = $post;
        $user = auth()->user();

        // Determine if paywall should show
        $this->showPaywall = $this->meteringService->shouldShowPaywall($post, $user);

        if ($this->showPaywall) {
            // Get paywall details
            $this->remainingFreeArticles = $this->meteringService->getRemainingFreeArticles($user);
            $this->paywallType = $this->meteringService->getPaywallType($post, $user);
            $this->tierDisplayName = $post->getTierDisplayName();
            $this->tierPrice = $post->getMinimumTierPrice();

            // Track paywall view
            $this->accessService->trackPaywallInteraction(
                $post,
                'view',
                $this->paywallType,
                $user
            );
        }
    }

    public function clickUpgrade()
    {
        // Track upgrade click
        $this->accessService->trackPaywallInteraction(
            $this->post,
            'click_upgrade',
            $this->paywallType,
            auth()->user()
        );

        // Redirect to pricing
        return redirect()->route('subscription.plans');
    }

    public function dismiss()
    {
        // Track dismissal
        $this->accessService->trackPaywallInteraction(
            $this->post,
            'dismiss',
            $this->paywallType,
            auth()->user()
        );

        $this->showPaywall = false;
    }

    public function render()
    {
        return view('livewire.progressive-paywall');
    }
}
