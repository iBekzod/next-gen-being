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
        // Set Paddle price IDs from config
        $this->plans['basic']['price_id'] = config('services.paddle.basic_price_id');
        $this->plans['pro']['price_id'] = config('services.paddle.pro_price_id');
        $this->plans['enterprise']['price_id'] = config('services.paddle.enterprise_price_id');
    }

    public function subscribe($planKey)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $plan = $this->plans[$planKey];

        try {
            $checkout = $user->checkout($plan['price_id'])
                ->returnTo(route('subscription.success'))
                ->create();

            return redirect($checkout->url);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create checkout: ' . $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.subscription-plans', [
            'currentSubscription' => Auth::check() ? Auth::user()->subscription : null,
        ]);
    }
}
