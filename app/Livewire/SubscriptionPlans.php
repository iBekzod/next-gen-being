<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\PaddleService;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlans extends Component
{
    public $plans;
    public $currentSubscription;
    private string $activeProvider;

    public function mount()
    {
        $this->activeProvider = config('services.subscription_provider', 'paddle');
        $this->currentSubscription = Auth::user()?->subscription;

        $this->plans = [
            'basic' => [
                'name' => 'Basic',
                'price' => 9.99,
                'interval' => 'month',
                'paddle_price_id' => config('services.paddle.basic_price_id'),
                'features' => [
                    'Access to premium articles',
                    'Ad-free reading experience',
                    'Basic analytics',
                    'Email support'
                ]
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 19.99,
                'interval' => 'month',
                'paddle_price_id' => config('services.paddle.pro_price_id'),
                'features' => [
                    'Everything in Basic',
                    'Early access to new content',
                    'Exclusive webinars',
                    'Priority support',
                    'Download articles as PDF'
                ]
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 49.99,
                'interval' => 'month',
                'paddle_price_id' => config('services.paddle.enterprise_price_id'),
                'features' => [
                    'Everything in Pro',
                    'Team accounts (up to 10 users)',
                    'Custom analytics dashboard',
                    'Dedicated account manager',
                    'API access'
                ]
            ]
        ];
    }

    public function subscribe(string $planKey)
    {
        if (!Auth::check()) {
            return redirect()->route('login', ['redirect' => route('subscription.plans')]);
        }

        $user = Auth::user();
        $plan = $this->plans[$planKey];

        try {
            if ($this->activeProvider === 'paddle') {
                $paddleService = app(PaddleService::class);
                $checkout = $paddleService->createCheckout([
                    'price_id' => $plan['paddle_price_id'],
                    'email' => $user->email,
                    'name' => $user->name,
                    'user_id' => $user->id,
                ]);

                return redirect($checkout['checkout_url']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create checkout: ' . $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.subscription-plans');
    }
}
