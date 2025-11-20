<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Service for handling OAuth provider operations
 */
class OAuthProviderService
{
    public function __construct(
        protected OAuthCredentialService $credentialService
    ) {}

    /**
     * Get Socialite user from OAuth provider
     *
     * @param string $provider Provider name
     * @return SocialiteUser OAuth user data
     * @throws \Exception If provider is invalid or not configured
     */
    public function getOAuthUser(string $provider): SocialiteUser
    {
        if (!$this->credentialService->isValidProvider($provider)) {
            throw new \Exception("Provider '{$provider}' is not valid.");
        }

        if (!$this->credentialService->hasValidCredentials($provider)) {
            throw new \Exception("Provider '{$provider}' credentials are not configured.");
        }

        try {
            return Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error("OAuth Error for provider {$provider}: " . $e->getMessage());
            throw new \Exception("Failed to authenticate with {$provider}: " . $e->getMessage());
        }
    }

    /**
     * Find or create user from OAuth provider
     *
     * @param string $provider Provider name
     * @param SocialiteUser $socialiteUser OAuth user data from Socialite
     * @return User The authenticated or created user
     */
    public function findOrCreateUser(string $provider, SocialiteUser $socialiteUser): User
    {
        // Try to find existing social account
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialiteUser->getId())
            ->first();

        if ($socialAccount) {
            return $socialAccount->user;
        }

        // Try to find user by email
        $user = User::where('email', $socialiteUser->getEmail())->first();

        // If user exists, link the social account
        if ($user) {
            $this->linkSocialAccount($user, $provider, $socialiteUser);
            return $user;
        }

        // Create new user and social account
        return $this->createUserWithSocialAccount($provider, $socialiteUser);
    }

    /**
     * Create a new user with social account
     *
     * @param string $provider Provider name
     * @param SocialiteUser $socialiteUser OAuth user data
     * @return User The newly created user
     */
    protected function createUserWithSocialAccount(string $provider, SocialiteUser $socialiteUser): User
    {
        $user = User::create([
            'name' => $socialiteUser->getName() ?? explode('@', $socialiteUser->getEmail())[0],
            'email' => $socialiteUser->getEmail(),
            'avatar' => $socialiteUser->getAvatar(),
            'email_verified_at' => now(),
            'password' => bcrypt(random_bytes(32)), // Random password for OAuth users
        ]);

        $this->linkSocialAccount($user, $provider, $socialiteUser);

        Log::info("New user created from OAuth: {$provider} - Email: {$user->email}");

        return $user;
    }

    /**
     * Link a social account to a user
     *
     * @param User $user The user to link the social account to
     * @param string $provider Provider name
     * @param SocialiteUser $socialiteUser OAuth user data
     * @return SocialAccount The created social account record
     */
    public function linkSocialAccount(User $user, string $provider, SocialiteUser $socialiteUser): SocialAccount
    {
        // Delete any existing social account for this provider and user
        SocialAccount::where('user_id', $user->id)
            ->where('provider', $provider)
            ->delete();

        // Create new social account
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'provider_email' => $socialiteUser->getEmail(),
            'provider_name' => $socialiteUser->getName(),
            'avatar_url' => $socialiteUser->getAvatar(),
            'access_token' => $socialiteUser->token ?? null,
            'refresh_token' => $socialiteUser->refreshToken ?? null,
            'expires_at' => isset($socialiteUser->expiresIn) ? now()->addSeconds($socialiteUser->expiresIn) : null,
            'metadata' => json_encode($socialiteUser->user ?? []),
        ]);

        Log::info("Social account linked: {$provider} to user {$user->id}");

        return $socialAccount;
    }

    /**
     * Disconnect a social account from user
     *
     * @param User $user The user to disconnect social account from
     * @param string $provider Provider name
     * @return bool True if disconnected, false if account was not found
     */
    public function disconnectSocialAccount(User $user, string $provider): bool
    {
        $deleted = SocialAccount::where('user_id', $user->id)
            ->where('provider', $provider)
            ->delete();

        if ($deleted) {
            Log::info("Social account disconnected: {$provider} from user {$user->id}");
            return true;
        }

        return false;
    }

    /**
     * Get user's connected social accounts
     *
     * @param User $user The user
     * @return array Array of connected provider names
     */
    public function getConnectedProviders(User $user): array
    {
        return $user->socialAccounts()
            ->pluck('provider')
            ->all();
    }

    /**
     * Check if user has a connected social account
     *
     * @param User $user The user
     * @param string $provider Provider name
     * @return bool True if user has this social account connected
     */
    public function hasConnectedProvider(User $user, string $provider): bool
    {
        return SocialAccount::where('user_id', $user->id)
            ->where('provider', $provider)
            ->exists();
    }

    /**
     * Get Socialite redirect instance for a provider
     *
     * @param string $provider Provider name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse Redirect response to OAuth provider
     * @throws \Exception If provider is invalid or not configured
     */
    public function getSocialiteRedirect(string $provider)
    {
        if (!$this->credentialService->isValidProvider($provider)) {
            throw new \Exception("Provider '{$provider}' is not valid.");
        }

        if (!$this->credentialService->hasValidCredentials($provider)) {
            throw new \Exception("Provider '{$provider}' credentials are not configured.");
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Refresh OAuth token if expired
     *
     * @param SocialAccount $socialAccount The social account record
     * @return bool True if token was refreshed, false if not needed
     */
    public function refreshTokenIfNeeded(SocialAccount $socialAccount): bool
    {
        if (!$socialAccount->refresh_token || !$socialAccount->expires_at) {
            return false;
        }

        if ($socialAccount->expires_at->isFuture()) {
            return false;
        }

        try {
            $provider = $socialAccount->provider;
            $response = \Http::post("https://oauth2.googleapis.com/token", [
                'client_id' => $this->credentialService->getClientId($provider),
                'client_secret' => $this->credentialService->getClientSecret($provider),
                'refresh_token' => $socialAccount->refresh_token,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $socialAccount->update([
                    'access_token' => $data['access_token'],
                    'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
                ]);

                Log::info("OAuth token refreshed for provider: {$provider}");
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("Failed to refresh OAuth token: " . $e->getMessage());
        }

        return false;
    }
}
