<?php

namespace App\Http\Controllers;

use App\Services\EnhancedAIGenerationService;
use App\Services\LemonSqueezyService;
use Illuminate\Http\Request;

class AiToolController extends Controller
{
    /** Display order for the pricing tiers. */
    private array $order = ['free', 'basic', 'premium', 'enterprise'];

    /**
     * Public landing / pricing page for the AI writing studio.
     */
    public function landing(EnhancedAIGenerationService $ai)
    {
        $tiers = [];
        foreach ($this->order as $key) {
            $t = $ai->getTierLimits($key);
            $t['key'] = $key;
            $t['name'] = ucfirst($key);
            $t['posts_label'] = $t['posts'] === null ? 'Unlimited' : $t['posts'] . ' posts/mo';
            $t['images_label'] = $t['images'] === null ? 'Unlimited' : $t['images'] . ' images/mo';
            $tiers[] = $t;
        }

        return view('ai-writer.landing', ['tiers' => $tiers]);
    }

    /**
     * Start a Lemon Squeezy checkout for a paid AI tier. The recurring
     * subscription webhook (subscription_created) upgrades the user's ai_tier.
     */
    public function upgrade(Request $request, string $tier, LemonSqueezyService $ls)
    {
        abort_unless(in_array($tier, ['basic', 'premium', 'enterprise'], true), 404);

        $variantId = config("services.lemonsqueezy.ai_{$tier}_variant_id");
        if (empty($variantId)) {
            return back()->with('error', "The {$tier} plan isn't available for purchase yet — please check back soon.");
        }

        $user = $request->user();

        $url = $ls->createCheckout([
            'variant_id' => $variantId,
            'checkout_data' => [
                'email' => $user->email,
                'name' => $user->name,
                // Echoed back on the subscription webhook so we can attribute a
                // first-time subscriber even before their LS customer is linked.
                'custom' => [
                    'user_id' => (string) $user->id,
                    'tier' => $tier,
                ],
            ],
            'product_options' => [
                'redirect_url' => route('ai-writer'),
            ],
            'preview' => false,
        ]);

        return $url
            ? redirect()->away($url)
            : back()->with('error', 'We couldn\'t start checkout just now. Please try again in a moment.');
    }
}
