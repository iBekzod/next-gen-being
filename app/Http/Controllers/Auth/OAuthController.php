<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    /**
     * Supported OAuth providers for user authentication
     */
    const SUPPORTED_PROVIDERS = ['google', 'github', 'facebook'];

    /**
     * Redirect to OAuth provider
     */
    public function redirect($provider)
    {
        if (!in_array($provider, self::SUPPORTED_PROVIDERS)) {
            abort(404, 'Provider not supported');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback
     */
    public function callback($provider)
    {
        if (!in_array($provider, self::SUPPORTED_PROVIDERS)) {
            abort(404, 'Provider not supported');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();

            // Find or create user
            $user = $this->findOrCreateUser($socialUser, $provider);

            // Log the user in
            Auth::login($user, true);

            // Redirect based on user role
            $redirectPath = $this->getRedirectPath($user);

            return redirect($redirectPath)->with('success', 'Welcome back, ' . $user->name . '!');

        } catch (\Exception $e) {
            \Log::error('OAuth Error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->with('error', 'Authentication failed. Please try again or use email/password.');
        }
    }

    /**
     * Find or create user from OAuth data
     */
    protected function findOrCreateUser($socialUser, $provider)
    {
        // First, try to find by provider ID
        $user = User::where('oauth_provider', $provider)
                    ->where('oauth_provider_id', $socialUser->getId())
                    ->first();

        if ($user) {
            // Update avatar if changed
            if ($socialUser->getAvatar() && $socialUser->getAvatar() !== $user->avatar) {
                $user->update(['avatar' => $socialUser->getAvatar()]);
            }
            return $user;
        }

        // Second, try to find by email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link this provider to existing account
            $user->update([
                'oauth_provider' => $provider,
                'oauth_provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
            return $user;
        }

        // Create new user
        return User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(32)), // Random password for OAuth users
            'email_verified_at' => now(), // OAuth emails are pre-verified
            'avatar' => $socialUser->getAvatar(),
            'oauth_provider' => $provider,
            'oauth_provider_id' => $socialUser->getId(),
        ]);
    }

    /**
     * Determine where to redirect user after login
     */
    protected function getRedirectPath(User $user)
    {
        // Check if user has admin role
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return '/admin';
        }

        // Check if user is a blogger or has posts
        if ((method_exists($user, 'hasRole') && $user->hasRole('blogger')) || $user->posts()->exists()) {
            return '/blogger';
        }

        // Default redirect
        return '/home';
    }
}
