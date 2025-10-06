<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionPlans extends Component
{
    public array $plans = [
        'basic' => [
            'name' => 'Basic',
            'price' => 9.99,
            'interval' => 'month',
            'features' => [
                'Premium articles',
                'Ad-free experience',
                'Baseline analytics'
            ],
            'stripe_price_id' => 'price_basic_monthly',
            'trial_days' => 7
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 19.99,
            'interval' => 'month',
            'features' => [
                'Everything in Basic',
                'Early access drops',
                'Exclusive webinars',
                'Downloadable PDF packs'
            ],
            'stripe_price_id' => 'price_pro_monthly',
            'trial_days' => 7
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 49.99,
            'interval' => 'month',
            'features' => [
                'Everything in Pro',
                'Team seats',
                'API access',
                'Dedicated success manager',
                'Custom analytics'
            ],
            'stripe_price_id' => 'price_enterprise_monthly',
            'trial_days' => 7
        ]
    ];

    public function subscribe($planKey)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $plan = $this->plans[$planKey];

        try {
            $checkout = $user->newSubscription('default', $plan['stripe_price_id'])
                ->checkout([
                    'success_url' => route('subscription.success'),
                    'cancel_url' => route('subscription.cancel'),
                ]);

            return redirect($checkout->url);
        } catch (IncompletePayment $exception) {
            return redirect()->route('cashier.payment', [
                $exception->payment->id,
                'redirect' => route('subscription.success')
            ]);
        }
    }

    public function render()
    {
        return view('livewire.subscription-plans', [
            'currentSubscription' => Auth::user()?->subscription(),
        ]);
    }
}
