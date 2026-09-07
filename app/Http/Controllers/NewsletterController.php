<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NewsletterService;
use App\Models\NewsletterEngagement;

class NewsletterController extends Controller
{
    protected $newsletterService;

    public function __construct(NewsletterService $newsletterService)
    {
        $this->newsletterService = $newsletterService;
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'frequency' => 'sometimes|in:daily,weekly,monthly',
        ]);

        $subscription = $this->newsletterService->subscribe(
            $request->email,
            auth()->id(),
            $request->input('frequency', 'weekly')
        );

        return response()->json([
            'success' => true,
            'message' => 'Please check your email to confirm your subscription.',
        ]);
    }

    public function verify($token)
    {
        $subscription = $this->newsletterService->verify($token);
        $secondClick = false;

        if (! $subscription) {
            // NewsletterService::verify() returns null both for an unknown token and for a
            // token that already belongs to a verified subscriber (e.g. the subscriber clicked
            // the confirmation link a second time). For an ordinary subscriber the second case
            // is indistinguishable from the first and the failure page is correct either way.
            // But when this link also hands over a prompt-magnet file (spec §7), a second click
            // must not strand the subscriber on a failure page just because they already
            // confirmed — re-check the token here to detect that specific case.
            $subscription = \App\Models\NewsletterSubscription::where('token', $token)
                ->whereNotNull('verified_at')
                ->first();

            $secondClick = true;
        }

        if (! $subscription) {
            return view('newsletter.verify-failed');
        }

        // Prompt magniti orqali kelgan bo'lsa, tasdiqlash bitta harakatda
        // faylni ham yetkazadi (spec §7): bitta ishqalanish nuqtasi.
        $slug = data_get($subscription->preferences, 'pending_prompt_listing');

        if ($slug) {
            $listing = \App\Models\MarketplaceListing::where('slug', $slug)
                ->where('status', 'published')
                ->first();

            if ($listing) {
                return redirect()->to(
                    $this->newsletterService->promptDownloadUrl($subscription, $listing)
                );
            }
        }

        if ($secondClick) {
            // Already verified, and no (valid) pending prompt to hand over — this is a plain
            // repeat visit to an ordinary confirmation link with nothing new to deliver, so
            // preserve the pre-existing failure-page behavior for that case.
            return view('newsletter.verify-failed');
        }

        return view('newsletter.verified', compact('subscription'));
    }

    public function unsubscribe($token)
    {
        $subscription = $this->newsletterService->unsubscribe($token);

        if ($subscription) {
            return view('newsletter.unsubscribed', compact('subscription'));
        }

        return redirect()->route('home')->with('error', 'Invalid unsubscribe link.');
    }

    public function preferences($token)
    {
        return view('newsletter.preferences', compact('token'));
    }

    public function updatePreferences(Request $request, $token)
    {
        $request->validate([
            'email' => 'required|email',
            'frequency' => 'required|in:daily,weekly,monthly',
            'categories' => 'sometimes|array',
        ]);

        $subscription = $this->newsletterService->updatePreferences($token, [
            'email' => $request->email,
            'frequency' => $request->frequency,
            'categories' => $request->input('categories', []),
        ]);

        if ($subscription) {
            return redirect()->back()->with('success', 'Preferences updated successfully!');
        }

        return redirect()->back()->with('error', 'Failed to update preferences.');
    }

    public function trackOpen($engagementId)
    {
        $this->newsletterService->trackOpen($engagementId);

        // Return 1x1 transparent pixel
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function trackClick($engagementId, Request $request)
    {
        $url = urldecode($request->query('url'));

        $redirectUrl = $this->newsletterService->trackClick($engagementId, $url);

        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        return redirect()->route('home');
    }
}
