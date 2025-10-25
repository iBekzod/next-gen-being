<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = [
            'basic' => [
                'name' => 'Basic',
                'price' => '$9.99',
                'interval' => 'month',
                'price_id' => config('services.lemonsqueezy.basic_variant_id'),
                'features' => [
                    'All premium articles & guides',
                    'Ad-free reading experience',
                    'Weekly newsletter'
                ]
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => '$19.99',
                'interval' => 'month',
                'price_id' => config('services.lemonsqueezy.pro_variant_id'),
                'features' => [
                    'Everything in Basic',
                    'Early access to new content',
                    'Monthly live webinars',
                    'Downloadable playbook library'
                ]
            ],
            'enterprise' => [
                'name' => 'Team',
                'price' => '$49.99',
                'interval' => 'month',
                'price_id' => config('services.lemonsqueezy.team_variant_id'),
                'features' => [
                    'Everything in Pro',
                    'Up to 10 team member accounts',
                    'Shared content library',
                    'Priority email support',
                    'Team usage insights'
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
            // Create LemonSqueezy checkout
            $checkout = $user->checkout($request->variant_id, [
                'name' => $user->name,
                'email' => $user->email,
            ]);

            // Get the checkout URL and redirect
            $checkoutUrl = $checkout->url();

            if ($checkoutUrl) {
                return redirect()->away($checkoutUrl);
            }

            return back()->with('error', 'Unable to create checkout session.');
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
        $subscription = $user->subscription();

        // Get the customer portal URL from LemonSqueezy
        if ($subscription && $subscription->lemon_squeezy_id) {
            $service = new \App\Services\LemonSqueezyService();
            $portalUrl = $service->getCustomerPortalUrl($subscription->lemon_squeezy_id);

            if ($portalUrl) {
                return redirect($portalUrl);
            }
        }

        return view('subscriptions.manage', [
            'subscription' => $subscription
        ]);
    }

    public function cancelSubscription()
    {
        $user = Auth::user();
        $subscription = $user->subscription();

        if (!$subscription || !$subscription->active()) {
            return back()->with('error', 'No active subscription found.');
        }

        try {
            $subscription->cancel();

            return back()->with('success', 'Subscription will be cancelled at the end of the current billing period.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }

    public function pauseSubscription()
    {
        $user = Auth::user();
        $subscription = $user->subscription();

        if (!$subscription || !$subscription->active()) {
            return back()->with('error', 'No active subscription found.');
        }

        try {
            $subscription->pause();

            return back()->with('success', 'Subscription paused successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to pause subscription: ' . $e->getMessage());
        }
    }

    public function resume()
    {
        $user = Auth::user();
        $subscription = $user->subscription();

        if (!$subscription) {
            return back()->with('error', 'No subscription found.');
        }

        try {
            if ($subscription->paused()) {
                $subscription->unpause();
                return back()->with('success', 'Subscription resumed successfully.');
            }

            if ($subscription->cancelled() && $subscription->onGracePeriod()) {
                $subscription->resume();
                return back()->with('success', 'Subscription resumed successfully.');
            }

            return back()->with('error', 'Subscription cannot be resumed.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resume subscription: ' . $e->getMessage());
        }
    }

    public function resumeSubscription()
    {
        return $this->resume();
    }
}
