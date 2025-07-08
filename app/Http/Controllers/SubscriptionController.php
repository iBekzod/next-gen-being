<?php
namespace App\Http\Controllers;

use App\Services\LemonSqueezyService;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(
        private LemonSqueezyService $lemonSqueezy
    ) {}

    public function plans()
    {
        $plans = [
            'basic' => [
                'name' => 'Basic',
                'price' => '$9.99',
                'interval' => 'month',
                'variant_id' => config('services.lemonsqueezy.basic_variant_id'),
                'features' => [
                    'Access to premium articles',
                    'Ad-free reading experience',
                    'Basic analytics',
                    'Email support'
                ]
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => '$19.99',
                'interval' => 'month',
                'variant_id' => config('services.lemonsqueezy.pro_variant_id'),
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
                'price' => '$49.99',
                'interval' => 'month',
                'variant_id' => config('services.lemonsqueezy.enterprise_variant_id'),
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
            'variant_id' => 'required|string'
        ]);

        $user = Auth::user();

        try {
            $checkout = $this->lemonSqueezy->createCheckout([
                'variant_id' => $request->variant_id,
                'email' => $user->email,
                'name' => $user->name,
                'user_id' => $user->id,
            ]);

            return redirect($checkout['data']['attributes']['url']);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create checkout: ' . $e->getMessage());
        }
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
            $this->lemonSqueezy->cancelSubscription($subscription->lemonsqueezy_id);
            $subscription->update(['status' => 'cancelled']);

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
            $this->lemonSqueezy->pauseSubscription($subscription->lemonsqueezy_id);
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

        if (!$subscription || !$subscription->isPaused()) {
            return back()->with('error', 'No paused subscription found.');
        }

        try {
            $this->lemonSqueezy->resumeSubscription($subscription->lemonsqueezy_id);
            $subscription->update(['status' => 'active']);

            return back()->with('success', 'Subscription resumed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resume subscription: ' . $e->getMessage());
        }
    }

    public function resume()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return back()->with('error', 'No subscription found.');
        }

        if ($subscription->isPaused()) {
            try {
                $this->lemonSqueezy->resumeSubscription($subscription->lemonsqueezy_id);
                $subscription->update(['status' => 'active']);

                return back()->with('success', 'Subscription resumed successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to resume subscription: ' . $e->getMessage());
            }
        } elseif ($subscription->isCancelled() && $subscription->ends_at && $subscription->ends_at->isFuture()) {
            // Resume a cancelled subscription that hasn't ended yet
            try {
                $this->lemonSqueezy->resumeSubscription($subscription->lemonsqueezy_id);
                $subscription->update([
                    'status' => 'active',
                    'ends_at' => null
                ]);

                return back()->with('success', 'Subscription resumed successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to resume subscription: ' . $e->getMessage());
            }
        } else {
            return back()->with('error', 'Subscription cannot be resumed.');
        }
    }
}
