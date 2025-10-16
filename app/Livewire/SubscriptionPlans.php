<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlans extends Component
{
    public array $plans = [
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

    public function subscribe($planKey)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $plan = $this->plans[$planKey] ?? null;

        if (!$plan || !$plan['price_id']) {
            session()->flash('error', 'Invalid subscription plan selected.');
            return;
        }

        // Check if LemonSqueezy is verified and ready
        if (config('services.lemonsqueezy.test_mode', false)) {
            session()->flash('info', '🚀 Premium subscriptions launching soon! We\'re finalizing payment processing. Check back within 24-48 hours.');
            return;
        }

        try {
            // Create LemonSqueezy checkout
            $checkout = Auth::user()->checkout($plan['price_id'], [
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ]);

            return $checkout;
        } catch (\Exception $e) {
            // If API key is not verified yet, show coming soon message
            if (str_contains($e->getMessage(), 'API key') || str_contains($e->getMessage(), 'not configured')) {
                session()->flash('info', '🚀 Premium subscriptions launching soon! We\'re finalizing payment processing. Check back within 24-48 hours.');
                return;
            }

            session()->flash('error', 'Unable to start checkout. Please try again later.');
            \Log::error('Subscription checkout error: ' . $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.subscription-plans', [
            'currentSubscription' => Auth::check() ? Auth::user()->subscription() : null,
        ]);
    }
}
