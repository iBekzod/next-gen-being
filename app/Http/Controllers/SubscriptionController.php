<?php
namespace App\Http\Controllers;

use App\Services\PaddleService;
use App\Services\LemonSqueezyService;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    private string $activeProvider;

    public function __construct(
        private PaddleService $paddle,
        private LemonSqueezyService $lemonSqueezy
    ) {
        // Choose your active provider - set to 'paddle' to switch
        $this->activeProvider = config('services.subscription_provider', 'paddle'); // or 'lemonsqueezy'
    }

    public function plans()
    {
        $plans = [
            'basic' => [
                'name' => 'Basic',
                'price' => 9.99,
                'interval' => 'month',
                'paddle_price_id' => config('services.paddle.basic_price_id'),
                'lemonsqueezy_variant_id' => config('services.lemonsqueezy.basic_variant_id'),
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
                'lemonsqueezy_variant_id' => config('services.lemonsqueezy.pro_variant_id'),
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
                'lemonsqueezy_variant_id' => config('services.lemonsqueezy.enterprise_variant_id'),
                'features' => [
                    'Everything in Pro',
                    'Team accounts (up to 10 users)',
                    'Custom analytics dashboard',
                    'Dedicated account manager',
                    'API access'
                ]
            ]
        ];

        return view('subscriptions.plans', compact('plans'));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:basic,pro,enterprise'
        ]);

        $user = Auth::user();
        $planKey = $request->input('plan');

        try {
            if ($this->activeProvider === 'paddle') {
                return $this->subscribePaddle($user, $planKey);
            } else {
                return $this->subscribeLemonSqueezy($user, $planKey);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create checkout: ' . $e->getMessage());
        }
    }

    private function subscribePaddle($user, string $planKey)
    {
        $plans = $this->getPlansConfig();
        $plan = $plans[$planKey];

        $checkout = $this->paddle->createCheckout([
            'price_id' => $plan['paddle_price_id'],
            'email' => $user->email,
            'name' => $user->name,
            'user_id' => $user->id,
        ]);

        return redirect($checkout['checkout_url']);
    }

    private function subscribeLemonSqueezy($user, string $planKey)
    {
        $plans = $this->getPlansConfig();
        $plan = $plans[$planKey];

        $checkout = $this->lemonSqueezy->createCheckout([
            'variant_id' => $plan['lemonsqueezy_variant_id'],
            'email' => $user->email,
            'name' => $user->name,
            'user_id' => $user->id,
        ]);

        return redirect($checkout['data']['attributes']['url']);
    }

    public function success()
    {
        return view('subscriptions.success');
    }

    public function cancel()
    {
        return view('subscriptions.cancel');
    }

    public function manage()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        return view('subscriptions.manage', compact('subscription'));
    }

    public function cancelSubscription()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->isActive()) {
            return back()->with('error', 'No active subscription found.');
        }

        try {
            if ($subscription->provider === 'paddle') {
                $this->paddle->cancelSubscription($subscription->provider_id);
            } else {
                $this->lemonSqueezy->cancelSubscription($subscription->provider_id);
            }

            $subscription->update([
                'status' => 'cancelled',
                'ends_at' => now()->addMonth() // or get from provider response
            ]);

            return back()->with('success', 'Subscription cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }

    public function pauseSubscription()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->isActive()) {
            return back()->with('error', 'No active subscription found.');
        }

        try {
            if ($subscription->provider === 'paddle') {
                $this->paddle->pauseSubscription($subscription->provider_id);
            } else {
                $this->lemonSqueezy->pauseSubscription($subscription->provider_id);
            }

            $subscription->update(['status' => 'paused']);

            return back()->with('success', 'Subscription paused successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to pause subscription: ' . $e->getMessage());
        }
    }

    public function resumeSubscription()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if (!$subscription || (!$subscription->isPaused() && !$subscription->isCancelled())) {
            return back()->with('error', 'No paused or cancelled subscription found.');
        }

        try {
            if ($subscription->provider === 'paddle') {
                $this->paddle->resumeSubscription($subscription->provider_id);
            } else {
                $this->lemonSqueezy->resumeSubscription($subscription->provider_id);
            }

            $subscription->update([
                'status' => 'active',
                'ends_at' => null
            ]);

            return back()->with('success', 'Subscription resumed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resume subscription: ' . $e->getMessage());
        }
    }

    private function getPlansConfig(): array
    {
        return [
            'basic' => [
                'name' => 'Basic',
                'price' => 9.99,
                'paddle_price_id' => config('services.paddle.basic_price_id'),
                'lemonsqueezy_variant_id' => config('services.lemonsqueezy.basic_variant_id'),
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 19.99,
                'paddle_price_id' => config('services.paddle.pro_price_id'),
                'lemonsqueezy_variant_id' => config('services.lemonsqueezy.pro_variant_id'),
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 49.99,
                'paddle_price_id' => config('services.paddle.enterprise_price_id'),
                'lemonsqueezy_variant_id' => config('services.lemonsqueezy.enterprise_variant_id'),
            ]
        ];
    }
}
