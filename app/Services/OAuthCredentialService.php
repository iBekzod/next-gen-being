<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

/**
 * Service for managing and validating OAuth provider credentials
 */
class OAuthCredentialService
{
    /**
     * List of available OAuth providers
     */
    protected const PROVIDERS = [
        'google',
        'github',
        'facebook',
        'discord',
    ];

    /**
     * Check if a provider has valid credentials configured
     *
     * @param string $provider Provider name (google, github, facebook, discord)
     * @return bool True if provider credentials are configured
     */
    public function hasValidCredentials(string $provider): bool
    {
        if (!$this->isValidProvider($provider)) {
            return false;
        }

        $clientId = $this->getClientId($provider);
        $clientSecret = $this->getClientSecret($provider);

        return !empty($clientId) && !empty($clientSecret);
    }

    /**
     * Get all providers with valid credentials
     *
     * @return array List of provider names with valid credentials
     */
    public function getEnabledProviders(): array
    {
        return collect(self::PROVIDERS)
            ->filter(fn ($provider) => $this->hasValidCredentials($provider))
            ->values()
            ->all();
    }

    /**
     * Get provider client ID from config or env
     *
     * @param string $provider Provider name
     * @return string|null Client ID or null if not configured
     */
    public function getClientId(string $provider): ?string
    {
        $provider = strtoupper($provider);

        // Try to get from config services first
        $configKey = "services.{$provider}.client_id";
        if (Config::has($configKey)) {
            return Config::get($configKey);
        }

        // Fall back to env variable
        return env("{$provider}_CLIENT_ID");
    }

    /**
     * Get provider client secret from config or env
     *
     * @param string $provider Provider name
     * @return string|null Client secret or null if not configured
     */
    public function getClientSecret(string $provider): ?string
    {
        $provider = strtoupper($provider);

        // Try to get from config services first
        $configKey = "services.{$provider}.client_secret";
        if (Config::has($configKey)) {
            return Config::get($configKey);
        }

        // Fall back to env variable
        return env("{$provider}_CLIENT_SECRET");
    }

    /**
     * Get provider redirect URI
     *
     * @param string $provider Provider name
     * @return string|null Redirect URI or null if not configured
     */
    public function getRedirectUri(string $provider): ?string
    {
        $provider = strtoupper($provider);

        // Try to get from config services first
        $configKey = "services.{$provider}.redirect";
        if (Config::has($configKey)) {
            return Config::get($configKey);
        }

        // Fall back to env variable or construct default
        $envUri = env("{$provider}_REDIRECT_URI");
        if ($envUri) {
            return $envUri;
        }

        // Construct default redirect URI
        return route('auth.social.callback', $provider);
    }

    /**
     * Check if provider name is valid
     *
     * @param string $provider Provider name
     * @return bool True if provider is in the list of available providers
     */
    public function isValidProvider(string $provider): bool
    {
        return in_array(strtolower($provider), self::PROVIDERS, true);
    }

    /**
     * Get provider info including display name and icon
     *
     * @param string $provider Provider name
     * @return array Provider information
     */
    public function getProviderInfo(string $provider): array
    {
        $provider = strtolower($provider);

        $info = [
            'google' => [
                'name' => 'Google',
                'icon' => 'google',
                'color' => '#4285F4',
                'display_name' => 'Sign in with Google',
            ],
            'github' => [
                'name' => 'GitHub',
                'icon' => 'github',
                'color' => '#333333',
                'display_name' => 'Sign in with GitHub',
            ],
            'facebook' => [
                'name' => 'Facebook',
                'icon' => 'facebook',
                'color' => '#1877F2',
                'display_name' => 'Sign in with Facebook',
            ],
            'discord' => [
                'name' => 'Discord',
                'icon' => 'discord',
                'color' => '#5865F2',
                'display_name' => 'Sign in with Discord',
            ],
        ];

        return $info[$provider] ?? [];
    }

    /**
     * Get all providers info
     *
     * @return array Array of all providers with their information
     */
    public function getAllProvidersInfo(): array
    {
        return collect(self::PROVIDERS)
            ->map(fn ($provider) => [
                'key' => $provider,
                ...$this->getProviderInfo($provider),
                'enabled' => $this->hasValidCredentials($provider),
            ])
            ->all();
    }
}
