<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlans extends Component
{
    public array $plans = [
        'free' => [
            'name' => 'Free',
            'price' => 0.00,
            'interval' => 'forever',
            'features' => [
                'Read all public articles',
                'Save bookmarks across devices',
                'Weekly newsletter',
                'Comment and follow authors'
            ],
            'price_id' => null,
            'trial_days' => 0,
        ],
        'basic' => [
            'name' => 'Basic',
            'price' => 9.99,
            'interval' => 'month',
            'features' => [
                'All premium articles & guides',
                'Ad-free reading experience',
                'Weekly newsletter'
            ],
            'price_id' => null, // Will be set in mount()
            'trial_days' => 7
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 19.99,
            'interval' => 'month',
            'features' => [
                'Everything in Basic',
                'Early access to new content',
                'Monthly live webinars',
                'Downloadable playbook library'
            ],
            'price_id' => null, // Will be set in mount()
            'trial_days' => 7
        ],
        'enterprise' => [
            'name' => 'Team',
            'price' => 49.99,
            'interval' => 'month',
            'features' => [
                'Everything in Pro',
                'Up to 10 team member accounts',
                'Shared content library',
                'Priority email support',
                'Team usage insights'
            ],
            'price_id' => null, // Will be set in mount()
            'trial_days' => 7
        ]
    ];

    public function mount()
    {
        // Set LemonSqueezy variant IDs from config
        $this->plans['basic']['price_id'] = config('services.lemonsqueezy.basic_variant_id');
        $this->plans['pro']['price_id'] = config('services.lemonsqueezy.pro_variant_id');
        $this->plans['enterprise']['price_id'] = config('services.lemonsqueezy.team_variant_id');
    }

    /**
     * Plan buttons are plain links to the checkout route; this stays so any
     * older wire:click caller still lands on the same flow.
     */
    public function subscribe($planKey)
    {
        if (! isset($this->plans[$planKey]) || $planKey === 'free') {
            session()->flash('error', 'Invalid subscription plan selected.');
            return;
        }

        return $this->redirect(route('subscription.checkout', $planKey));
    }

    public function render()
    {
        return view('livewire.subscription-plans', [
            'currentSubscription' => Auth::check() ? Auth::user()->subscription() : null,
        ]);
    }
}
