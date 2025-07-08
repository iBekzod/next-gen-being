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
                'Access to premium articles',
                'Ad-free reading experience',
                'Basic analytics',
                'Email support'
            ],
            'stripe_price_id' => 'price_basic_monthly'
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 19.99,
            'interval' => 'month',
            'features' => [
                'Everything in Basic',
                'Early access to new content',
                'Exclusive webinars',
                'Priority support',
                'Download articles as PDF'
            ],
            'stripe_price_id' => 'price_pro_monthly'
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 49.99,
            'interval' => 'month',
            'features' => [
                'Everything in Pro',
                'Team accounts (up to 10 users)',
                'Custom analytics dashboard',
                'Dedicated account manager',
                'API access'
            ],
            'stripe_price_id' => 'price_enterprise_monthly'
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
