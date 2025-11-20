<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuthCredentialService;
use App\Services\OAuthProviderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class OAuthController extends Controller
{
    public function __construct(
        protected OAuthCredentialService $credentialService,
        protected OAuthProviderService $providerService,
    ) {}

    /**
     * Redirect to OAuth provider
     */
    public function redirect(string $provider): RedirectResponse
    {
        // Validate provider is enabled
        if (!$this->credentialService->hasValidCredentials($provider)) {
            return redirect()->route('login')
                ->with('error', ucfirst($provider) . ' authentication is not available right now.');
        }

        try {
            return $this->providerService->getSocialiteRedirect($provider);
        } catch (\Exception $e) {
            \Log::error('OAuth Redirect Error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Failed to redirect to ' . ucfirst($provider) . '. Please try again.');
        }
    }

    /**
     * Handle OAuth callback
     */
    public function callback(string $provider): RedirectResponse
    {
        // Validate provider is enabled
        if (!$this->credentialService->hasValidCredentials($provider)) {
            return redirect()->route('login')
                ->with('error', ucfirst($provider) . ' authentication is not available.');
        }

        try {
            // Get user from OAuth provider
            $socialiteUser = $this->providerService->getOAuthUser($provider);

            // Find or create user and link social account
            $user = $this->providerService->findOrCreateUser($provider, $socialiteUser);

            // Log the user in
            Auth::login($user, true);

            // Redirect based on user role
            $redirectPath = $this->getRedirectPath($user);

            return redirect($redirectPath)
                ->with('success', 'Welcome back, ' . $user->name . '! You have successfully signed in with ' . ucfirst($provider) . '.');

        } catch (\Exception $e) {
            \Log::error('OAuth Callback Error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->with('error', 'Authentication failed. Please try again or use email/password.');
        }
    }

    /**
     * Disconnect OAuth provider from user account
     */
    public function disconnect(string $provider): RedirectResponse
    {
        // Validate provider
        if (!$this->credentialService->isValidProvider($provider)) {
            return redirect()->back()
                ->with('error', 'Invalid provider.');
        }

        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login');
            }

            // Check if user has this provider connected
            if (!$this->providerService->hasConnectedProvider($user, $provider)) {
                return redirect()->back()
                    ->with('error', ucfirst($provider) . ' is not connected to your account.');
            }

            // Disconnect the social account
            $this->providerService->disconnectSocialAccount($user, $provider);

            return redirect()->back()
                ->with('success', ucfirst($provider) . ' has been successfully disconnected from your account.');

        } catch (\Exception $e) {
            \Log::error('OAuth Disconnect Error', [
                'provider' => $provider,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to disconnect ' . ucfirst($provider) . '. Please try again.');
        }
    }

    /**
     * Determine where to redirect user after login
     */
    protected function getRedirectPath($user): string
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
