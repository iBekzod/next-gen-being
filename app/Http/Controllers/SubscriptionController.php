<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = [
            'free' => [
                'name' => 'Free',
                'price' => '$0',
                'interval' => 'forever',
                'price_id' => null,
                'features' => [
                    'Read all public articles',
                    'Save bookmarks across devices',
                    'Weekly newsletter',
                    'Comment and follow authors',
                ]
            ],
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

    /**
     * Plan key (as used on /pricing) => config key holding its Lemon Squeezy variant id.
     */
    private const PLAN_VARIANTS = [
        'basic' => 'basic_variant_id',
        'pro' => 'pro_variant_id',
        'enterprise' => 'team_variant_id',
    ];

    /**
     * One click from /pricing to the hosted Lemon Squeezy checkout.
     *
     * Logged in: the checkout is pre-filled with the user's name and email and
     * carries user_id (plus the package's billable_id/type) so the webhook links
     * the subscription to them. Guest: straight to checkout with guest_checkout=1;
     * Lemon Squeezy collects the email and ProvisionGuestCheckoutBillable creates
     * or links the account when the webhook arrives.
     */
    public function checkout(Request $request, string $plan)
    {
        $variantId = isset(self::PLAN_VARIANTS[$plan])
            ? (string) config('services.lemonsqueezy.' . self::PLAN_VARIANTS[$plan])
            : '';

        if ($variantId === '') {
            abort_unless(isset(self::PLAN_VARIANTS[$plan]), 404);

            return redirect()->route('subscription.plans')
                ->with('error', 'This plan is not available right now. Please try again soon.');
        }

        $user = Auth::user();

        try {
            if ($user) {
                $checkout = $user->checkout($variantId, [
                    'name' => $user->name,
                    'email' => $user->email,
                ], [
                    'user_id' => (string) $user->id,
                    'plan' => $plan,
                ])->redirectTo(route('subscription.success'));
            } else {
                $checkout = \LemonSqueezy\Laravel\Checkout::make((string) config('lemon-squeezy.store'), $variantId)
                    ->withCustomData(['guest_checkout' => '1', 'plan' => $plan])
                    ->redirectTo(route('subscription.welcome'));
            }

            return redirect()->away($checkout->url());
        } catch (\Throwable $e) {
            \Log::error('Pricing checkout failed', [
                'plan' => $plan,
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('subscription.plans')
                ->with('error', 'We could not start checkout just now. Please try again in a moment.');
        }
    }

    public function welcome()
    {
        return view('subscriptions.welcome');
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
            ], ['user_id' => (string) $user->id]);

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
