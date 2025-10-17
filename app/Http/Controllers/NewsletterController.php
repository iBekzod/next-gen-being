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

        if ($subscription) {
            return view('newsletter.verified', compact('subscription'));
        }

        return view('newsletter.verify-failed');
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
