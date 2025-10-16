<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserDashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'total_posts' => $user->posts()->count(),
            'published_posts' => $user->posts()->where('status', 'published')->count(),
            'total_views' => $user->posts()->sum('views_count'),
            'total_likes' => $user->posts()->sum('likes_count'),
            'total_comments' => $user->posts()->sum('comments_count'),
            'bookmarks' => $user->interactions()->where('type', 'bookmark')->count(),
        ];

        return view('dashboard.index', compact('stats'));
    }

    /**
     * Display the user's posts.
     */
    public function posts(Request $request): View
    {
        $filter = $request->get('filter', 'all');

        return view('dashboard.posts', [
            'filter' => $filter,
        ]);
    }

    /**
     * Display the user's bookmarked posts.
     */
    public function bookmarks(): View
    {
        return view('dashboard.bookmarks');
    }

    /**
     * Display the user settings page.
     */
    public function settings(): View
    {
        $user = Auth::user();

        return view('dashboard.settings', [
            'user' => $user,
            'subscription' => $user->subscription,
        ]);
    }

    /**
     * Update user settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'twitter' => 'nullable|string|max:50',
            'linkedin' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')
                ->toMediaCollection('avatars');

            $validated['avatar'] = $user->getFirstMediaUrl('avatars');
        }

        $user->update($validated);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The password is incorrect.',
            ]);
        }

        // Cancel any active subscriptions (LemonSqueezy will handle this automatically)
        if ($user->subscribed()) {
            try {
                $user->subscription()->cancel();
            } catch (\Exception $e) {
                // Log the error but continue with deletion
                \Log::error('Failed to cancel subscription during account deletion: ' . $e->getMessage());
            }
        }

        // Delete the user
        $user->delete();

        Auth::logout();

        return redirect()->route('home')
            ->with('success', 'Your account has been deleted successfully.');
    }
}
