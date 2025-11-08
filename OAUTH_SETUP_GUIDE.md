# OAuth Social Login Setup Guide

This guide explains how to set up social authentication (OAuth) for your NextGenBeing platform with Google, GitHub, and Discord.

## Prerequisites

Before getting started, ensure you have:
1. Composer installed
2. A `.env` file configured
3. Access to OAuth provider dashboards
4. Your application URL (for redirect URIs)

## Step 1: Install Laravel Socialite

First, install the Laravel Socialite package via Composer:

```bash
composer require laravel/socialite
```

## Step 2: Configure OAuth Providers

### Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a new project or select an existing one
3. Enable the Google+ API
4. Create OAuth 2.0 credentials (Web Application):
   - Set authorized redirect URIs: `https://yourdomain.com/auth/oauth/google/callback`
5. Copy your **Client ID** and **Client Secret**

Add to your `.env` file:

```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_OAUTH_REDIRECT=https://yourdomain.com/auth/oauth/google/callback
```

### GitHub OAuth Setup

1. Go to [GitHub Settings - Developer Applications](https://github.com/settings/developers)
2. Click "New OAuth App"
3. Fill in the form:
   - **Application name**: NextGenBeing
   - **Homepage URL**: `https://yourdomain.com`
   - **Authorization callback URL**: `https://yourdomain.com/auth/oauth/github/callback`
4. Copy your **Client ID** and **Client Secret**

Add to your `.env` file:

```env
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_OAUTH_REDIRECT=https://yourdomain.com/auth/oauth/github/callback
```

### Discord OAuth Setup

1. Go to [Discord Developer Portal](https://discord.com/developers/applications)
2. Click "New Application"
3. Go to OAuth2 > General
4. Add redirect URL: `https://yourdomain.com/auth/oauth/discord/callback`
5. Go to OAuth2 > Client Information
6. Copy your **Client ID** and **Client Secret**

Add to your `.env` file:

```env
DISCORD_CLIENT_ID=your_discord_client_id
DISCORD_CLIENT_SECRET=your_discord_client_secret
DISCORD_OAUTH_REDIRECT=https://yourdomain.com/auth/oauth/discord/callback
```

## Step 3: Run Migrations

After configuring the database connection, run the migrations to create the necessary tables:

```bash
php artisan migrate
```

This will create:
- `social_accounts` table - stores connected OAuth accounts
- Updates to `users` table with oauth fields

## Step 4: Add Social Login Buttons to Your Login Page

Update your login view (typically `resources/views/auth/login.blade.php`) to include social login buttons:

```blade
<div class="mt-6">
    <h3 class="text-center text-sm font-medium text-gray-900 mb-4">
        Or continue with
    </h3>

    <x-social-auth-buttons />
</div>
```

Or for a simpler implementation:

```blade
<a href="{{ route('auth.social.redirect', 'google') }}" class="btn btn-primary w-full">
    Sign in with Google
</a>

<a href="{{ route('auth.social.redirect', 'github') }}" class="btn btn-dark w-full mt-2">
    Sign in with GitHub
</a>

<a href="{{ route('auth.social.redirect', 'discord') }}" class="btn btn-indigo w-full mt-2">
    Sign in with Discord
</a>
```

## Step 5: Manage Connected Accounts

Users can view and manage their connected accounts in their settings page. Add this to your user settings view:

```blade
<x-social-account-manager />
```

This component allows users to:
- View all connected OAuth accounts
- Connect new accounts
- Disconnect existing accounts (if they have a password)

## Features

### What's Implemented

✅ **OAuth Authentication**
- Users can sign up and log in with Google, GitHub, or Discord
- Automatic account creation if user doesn't exist
- Account linking if user has existing email

✅ **Account Management**
- Users can connect/disconnect multiple OAuth providers
- View connected account details
- Secure token storage (encrypted in database)

✅ **Database Schema**
- `social_accounts` table with proper foreign keys and indexes
- OAuth provider information stored securely
- Access tokens and refresh tokens encrypted

✅ **Routes**
- `/auth/oauth/{provider}/redirect` - Initiate OAuth flow
- `/auth/oauth/{provider}/callback` - Handle OAuth callback
- `/auth/oauth/{provider}/disconnect` - Disconnect OAuth account

### Security Features

- OAuth tokens are stored securely
- Automatic email verification for OAuth users
- User can only disconnect if they have an alternative login method
- Proper error handling and logging

## Troubleshooting

### "Provider not supported"
- Ensure you're using a valid provider: `google`, `github`, `facebook`, or `discord`
- Check that the provider name matches the route parameter

### "Failed to authenticate"
- Verify your OAuth credentials in `.env` are correct
- Check that your redirect URI matches exactly in the OAuth provider dashboard
- Ensure the OAuth provider service is operational

### "Callback URL mismatch"
- The callback URL in your OAuth provider settings must match exactly: `https://yourdomain.com/auth/oauth/{provider}/callback`
- No trailing slashes or query parameters

### Token expiration
- If users get an "authentication failed" after a while, their refresh token may have expired
- They can re-authenticate with the provider to update their token

## Advanced Configuration

### Supported OAuth Providers

The system is pre-configured for:
- ✅ Google
- ✅ GitHub
- ✅ Discord
- ⚙️ Facebook (requires Socialite Facebook driver)

### Adding More Providers

To add additional providers (Twitter, LinkedIn, etc.):

1. Add configuration to `config/services.php`
2. Update the `SUPPORTED_PROVIDERS` in `OAuthController`
3. Update the route where clauses to include the new provider
4. Create corresponding Blade components

## User Experience Flow

### First Time User with OAuth
1. User clicks "Sign in with [Provider]"
2. Redirected to provider's login/permission page
3. Authorized, returns to app
4. New user account created automatically
5. Logged in and redirected to dashboard

### Existing User with OAuth
1. User clicks "Sign in with [Provider]"
2. OAuth provider email matches existing account
3. Account automatically linked
4. Logged in and redirected to dashboard

### Disconnecting OAuth

Users can disconnect an OAuth account from settings if they have:
- A password set (can log in with email/password), OR
- Another OAuth account connected

This ensures users always have a way to log in.

## Environment Variables Reference

```env
# Google OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_OAUTH_REDIRECT=https://yourdomain.com/auth/oauth/google/callback

# GitHub OAuth
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_OAUTH_REDIRECT=https://yourdomain.com/auth/oauth/github/callback

# Discord OAuth
DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_OAUTH_REDIRECT=https://yourdomain.com/auth/oauth/discord/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_OAUTH_REDIRECT=https://yourdomain.com/auth/oauth/facebook/callback
```

## Testing OAuth Locally

When testing locally with `localhost`:

1. Update your OAuth provider to accept `http://localhost:8000/auth/oauth/{provider}/callback`
2. Update `.env`:
```env
GOOGLE_OAUTH_REDIRECT=http://localhost:8000/auth/oauth/google/callback
GITHUB_OAUTH_REDIRECT=http://localhost:8000/auth/oauth/github/callback
DISCORD_OAUTH_REDIRECT=http://localhost:8000/auth/oauth/discord/callback
```

Note: Some providers (Discord) are more flexible with localhost testing. Others (Google) may require whitelisting in the OAuth app settings.

## Database Schema

### social_accounts table

```
id: unsigned bigint
user_id: unsigned bigint (FK → users.id)
provider: string (google, github, discord, etc.)
provider_id: string (unique per provider)
provider_email: string (nullable)
provider_name: string (nullable)
avatar_url: string (nullable)
access_token: longText (encrypted)
refresh_token: longText (nullable, encrypted)
expires_at: timestamp (nullable)
metadata: json (additional provider data)
created_at, updated_at: timestamps

Indexes:
- unique(provider, provider_id)
- index(user_id)
- index(provider)
```

### users table updates

```
oauth_provider: string (nullable)
oauth_provider_id: string (nullable)
password_updated_at: timestamp (nullable)

Index:
- index(oauth_provider, oauth_provider_id)
```

## API Integration

The system stores OAuth tokens in the database, allowing you to:

1. **Access user's OAuth provider data**
```php
$socialAccount = auth()->user()->socialAccounts()->first();
$accessToken = $socialAccount->access_token;
```

2. **Check if user has OAuth account**
```php
if (auth()->user()->socialAccounts()->where('provider', 'github')->exists()) {
    // User has GitHub connected
}
```

3. **Get provider metadata**
```php
$metadata = $socialAccount->metadata;
$providerId = $socialAccount->provider_id;
```

## Support

For issues or questions:
1. Check Socialite documentation: https://laravel.com/docs/socialite
2. Verify OAuth provider settings
3. Check application logs for error messages
4. Ensure all environment variables are set correctly

## Security Considerations

- Always use HTTPS in production
- Store OAuth secrets in `.env` (never in version control)
- Regularly rotate OAuth provider credentials
- Monitor user account access
- Log authentication attempts
- Use proper CORS headers if needed
