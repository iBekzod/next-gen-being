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
                'price_id' => config('services.paddle.basic_price_id'),
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
                'price_id' => config('services.paddle.pro_price_id'),
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
                'price_id' => config('services.paddle.enterprise_price_id'),
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
            'price_id' => 'required|string'
        ]);

        $user = Auth::user();

        try {
            $checkout = $user->checkout($request->price_id)
                ->returnTo(route('subscription.success'))
                ->create();

            return redirect($checkout->url);
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

        // Get the customer's portal session from Paddle
        if ($user->customer) {
            try {
                $session = $user->customer->session([
                    'return_url' => route('subscription.manage'),
                ]);

                return redirect($session->url);
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to load subscription management: ' . $e->getMessage());
            }
        }

        return view('subscriptions.manage', [
            'subscription' => $user->subscription
        ]);
    }

    public function cancelSubscription()
    {
        $user = Auth::user();

        if (!$user->subscription || !$user->subscription->active()) {
            return back()->with('error', 'No active subscription found.');
        }

        try {
            $user->subscription->cancel();

            return back()->with('success', 'Subscription will be cancelled at the end of the current billing period.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }

    public function pauseSubscription()
    {
        $user = Auth::user();

        if (!$user->subscription || !$user->subscription->active()) {
            return back()->with('error', 'No active subscription found.');
        }

        try {
            $user->subscription->pause();

            return back()->with('success', 'Subscription paused successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to pause subscription: ' . $e->getMessage());
        }
    }

    public function resume()
    {
        $user = Auth::user();

        if (!$user->subscription) {
            return back()->with('error', 'No subscription found.');
        }

        try {
            if ($user->subscription->paused()) {
                $user->subscription->unpause();
                return back()->with('success', 'Subscription resumed successfully.');
            }

            if ($user->subscription->cancelled() && $user->subscription->onGracePeriod()) {
                $user->subscription->resume();
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
