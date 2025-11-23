<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\AffiliateService;

class AffiliateManager extends Component
{
    use WithPagination;

    public AffiliateService $affiliateService;
    public $links = [];
    public $stats = [];
    public $totalEarnings = 0;
    public $isLoading = true;

    public function mount()
    {
        $this->affiliateService = app(AffiliateService::class);
        $this->loadAffiliates();
    }

    public function loadAffiliates()
    {
        $this->isLoading = true;

        try {
            $user = auth()->user();

            $this->links = $user->affiliateLinks()
                ->with('clicks', 'conversions')
                ->paginate(10);

            $this->stats = $this->affiliateService->getAffiliateStats($user);
            $this->totalEarnings = $this->affiliateService->getAffiliateEarnings($user);
        } finally {
            $this->isLoading = false;
        }
    }

    public function createLink()
    {
        $this->dispatch('openCreateLinkModal');
    }

    public function storeLink($url, $description = '')
    {
        try {
            $result = $this->affiliateService->createAffiliateLink(
                auth()->user(),
                $url,
                $description
            );

            if ($result['success']) {
                $this->dispatch('linkCreated');
                $this->loadAffiliates();
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to create link');
        }
    }

    public function render()
    {
        return view('livewire.affiliate-manager');
    }
}
