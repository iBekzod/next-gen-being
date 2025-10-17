<?php

namespace App\Livewire;

use Livewire\Component;

class TrialExpiryBanner extends Component
{
    public $showBanner = false;
    public $daysLeft = 0;

    public function mount()
    {
        $user = auth()->user();

        if ($user && $user->onTrial() && $user->trial_ends_at) {
            $this->daysLeft = now()->diffInDays($user->trial_ends_at);

            // Show banner only in last 3 days
            $this->showBanner = $this->daysLeft <= 3;
        }
    }

    public function dismiss()
    {
        $this->showBanner = false;
    }

    public function render()
    {
        return view('livewire.trial-expiry-banner');
    }
}
