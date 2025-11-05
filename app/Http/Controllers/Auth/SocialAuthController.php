<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    /**
     * Supported social media platforms
     */
    protected const SUPPORTED_PLATFORMS = [
        'youtube' => 'google',
        'instagram' => 'instagram',
        'facebook' => 'facebook',
        'twitter' => 'twitter',
        'linkedin' => 'linkedin-openid',
    ];

    /**
     * Redirect user to OAuth provider
     *
     * @param string $platform
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(string $platform)
    {
        if (!$this->isPlatformSupported($platform)) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Platform not supported');
        }

        try {
            $provider = $this->getProviderName($platform);

            // Add additional scopes based on platform
            $scopes = $this->getPlatformScopes($platform);

            return Socialite::driver($provider)
                ->scopes($scopes)
                ->redirect();

        } catch (Exception $e) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Failed to connect to ' . ucfirst($platform) . ': ' . $e->getMessage());
        }
    }

    /**
     * Handle OAuth callback
     *
     * @param string $platform
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(string $platform)
    {
        if (!$this->isPlatformSupported($platform)) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Platform not supported');
        }

        try {
            $provider = $this->getProviderName($platform);
            $socialUser = Socialite::driver($provider)->user();

            // Store or update social media account
            $this->storeSocialMediaAccount($platform, $socialUser);

            return redirect()->route('filament.admin.pages.dashboard')
                ->with('success', ucfirst($platform) . ' account connected successfully!');

        } catch (Exception $e) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Failed to connect ' . ucfirst($platform) . ': ' . $e->getMessage());
        }
    }

    /**
     * Disconnect a social media account
     *
     * @param int $accountId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect(int $accountId)
    {
        $account = SocialMediaAccount::where('user_id', Auth::id())
            ->findOrFail($accountId);

        $platform = $account->platform;
        $account->delete();

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('success', ucfirst($platform) . ' account disconnected successfully!');
    }

    /**
     * Store or update social media account
     */
    protected function storeSocialMediaAccount(string $platform, $socialUser): SocialMediaAccount
    {
        $expiresAt = null;
        if (isset($socialUser->expiresIn)) {
            $expiresAt = now()->addSeconds($socialUser->expiresIn);
        }

        return SocialMediaAccount::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'platform' => $platform,
                'platform_user_id' => $socialUser->getId(),
            ],
            [
                'platform_username' => $socialUser->getNickname() ?? $socialUser->getName() ?? $socialUser->getEmail(),
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken ?? null,
                'token_expires_at' => $expiresAt,
                'account_type' => 'personal',
            ]
        );
    }

    /**
     * Check if platform is supported
     */
    protected function isPlatformSupported(string $platform): bool
    {
        return array_key_exists($platform, self::SUPPORTED_PLATFORMS);
    }

    /**
     * Get Socialite provider name for platform
     */
    protected function getProviderName(string $platform): string
    {
        return self::SUPPORTED_PLATFORMS[$platform] ?? $platform;
    }

    /**
     * Get platform-specific OAuth scopes
     */
    protected function getPlatformScopes(string $platform): array
    {
        return match($platform) {
            'youtube' => [
                'https://www.googleapis.com/auth/youtube.upload',
                'https://www.googleapis.com/auth/youtube.readonly',
            ],
            'instagram' => [
                'instagram_basic',
                'instagram_content_publish',
            ],
            'facebook' => [
                'pages_manage_posts',
                'pages_read_engagement',
                'pages_show_list',
            ],
            'twitter' => [
                'tweet.read',
                'tweet.write',
                'users.read',
            ],
            'linkedin' => [
                'w_member_social',
                'r_liteprofile',
                'r_basicprofile',
            ],
            default => [],
        };
    }
}
