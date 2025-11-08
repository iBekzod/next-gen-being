# NextGenBeing - Complete Setup Instructions

This is the master setup guide for NextGenBeing. Follow these steps to get your application running locally and in production.

## 📋 Table of Contents
1. [Local Development Setup](#local-development-setup)
2. [Database Setup](#database-setup)
3. [OAuth Configuration](#oauth-configuration)
4. [Feature Integration](#feature-integration)
5. [Production Deployment](#production-deployment)

---

## 🚀 Local Development Setup

### Prerequisites
- PHP 8.4+
- Node.js 20+
- PostgreSQL 15
- Composer
- Git

### Step 1: Clone and Install Dependencies

```bash
# Clone the repository
git clone https://github.com/yourusername/next-gen-being.git
cd next-gen-being

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate
```

### Step 2: Configure Environment

Edit `.env` file with your local settings:

```env
APP_NAME="NextGenBeing"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nextgenbeing
DB_USERNAME=postgres
DB_PASSWORD=password

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=cookie
QUEUE_CONNECTION=redis

# Mail (for local testing, use log)
MAIL_MAILER=log
```

### Step 3: Setup Database

```bash
# Create database
createdb nextgenbeing

# Run migrations
php artisan migrate

# Seed initial data (optional)
php artisan db:seed
```

### Step 4: Build Frontend Assets

```bash
# Development mode
npm run dev

# Or production build
npm run build
```

### Step 5: Start Development Server

```bash
# In one terminal
php artisan serve

# In another terminal (watch assets)
npm run dev

# Optional: Start queue worker
php artisan queue:work
```

Visit: `http://localhost:8000`

---

## 🗄️ Database Setup

### PostgreSQL Installation

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install postgresql postgresql-contrib
sudo systemctl start postgresql
```

**macOS (Homebrew):**
```bash
brew install postgresql@15
brew services start postgresql@15
```

**Windows (WSL):**
```bash
sudo apt-get update
sudo apt-get install postgresql postgresql-contrib
```

### Create Database & User

```bash
# Connect to PostgreSQL
psql -U postgres

# Create database
CREATE DATABASE nextgenbeing;

# Create user
CREATE USER nextgen_user WITH PASSWORD 'your_strong_password';

# Grant privileges
ALTER ROLE nextgen_user SET client_encoding TO 'utf8';
ALTER ROLE nextgen_user SET default_transaction_isolation TO 'read committed';
ALTER ROLE nextgen_user SET default_transaction_deferrable TO on;
ALTER ROLE nextgen_user SET timezone TO 'UTC';
GRANT ALL PRIVILEGES ON DATABASE nextgenbeing TO nextgen_user;

# Exit
\q
```

---

## 🔐 OAuth Configuration

### Step 1: Install Socialite

```bash
composer require laravel/socialite
```

### Step 2: Setup Google OAuth

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create new project
3. Enable Google+ API
4. Create OAuth 2.0 credentials (Web Application)
5. Add authorized redirect URI: `http://localhost:8000/auth/oauth/google/callback`
6. Copy Client ID and Client Secret

Add to `.env`:
```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_OAUTH_REDIRECT=http://localhost:8000/auth/oauth/google/callback
```

### Step 3: Setup GitHub OAuth

1. Go to [GitHub Settings > Developer Applications](https://github.com/settings/developers)
2. Click "New OAuth App"
3. Fill in application details
4. Set Authorization callback URL: `http://localhost:8000/auth/oauth/github/callback`
5. Copy Client ID and Client Secret

Add to `.env`:
```env
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_client_secret
GITHUB_OAUTH_REDIRECT=http://localhost:8000/auth/oauth/github/callback
```

### Step 4: Setup Discord OAuth

1. Go to [Discord Developer Portal](https://discord.com/developers/applications)
2. Create New Application
3. Go to OAuth2 > General
4. Add redirect URL: `http://localhost:8000/auth/oauth/discord/callback`
5. Copy Client ID and Client Secret

Add to `.env`:
```env
DISCORD_CLIENT_ID=your_client_id
DISCORD_CLIENT_SECRET=your_client_secret
DISCORD_OAUTH_REDIRECT=http://localhost:8000/auth/oauth/discord/callback
```

### Step 5: Update Login Page

Add to `resources/views/auth/login.blade.php`:

```blade
<!-- After email/password form -->
<div class="mt-6">
    <h3 class="text-center text-sm font-medium text-gray-900 mb-4">
        Or continue with
    </h3>
    <x-social-auth-buttons />
</div>
```

### Step 6: Add Account Manager to Settings

Add to user settings page (e.g., `resources/views/profile/settings.blade.php`):

```blade
<div class="mt-8">
    <h2 class="text-2xl font-bold mb-4">Connected Accounts</h2>
    <x-social-account-manager />
</div>
```

---

## 🎯 Feature Integration

### AI Learning System

The AI learning system is pre-built. To use it:

1. **View learning paths** in dashboard (auto-populated if created)
2. **Get recommendations** - automatically shown on dashboard
3. **View insights** - access learning progress and analytics

All components are in place:
- Models: `app/Models/LearningPath.php`, `LearningPathItem.php`, `AIRecommendation.php`
- Services: `app/Services/AI/`
- UI Components: `resources/views/components/ai-*`

### Tutorial Progress Tracking

Already implemented via:
- Model: `app/Models/TutorialProgress.php`
- Service: `app/Services/Tutorial/TutorialProgressService.php`
- Components: `resources/views/components/learning-progress-card.blade.php`, `leaderboard.blade.php`

### Achievement System

Already integrated:
- Model: `app/Models/Achievement.php`
- Auto-awarded based on progress
- Display components in place

---

## 🚀 Production Deployment

### Prerequisites
- Linux server (Ubuntu 22.04 recommended)
- PHP 8.4 with required extensions
- PostgreSQL 15
- Nginx
- Supervisor
- Redis

### Step 1: Server Initial Setup

See [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for complete server setup instructions including:
- User creation
- PHP-FPM setup
- Nginx configuration
- Database setup
- SSL/HTTPS

### Step 2: Configure GitHub Secrets

Add to GitHub repository secrets:

1. `SERVER_IP` - Your server's IP address
2. `SSH_USER` - SSH username for deployment
3. `SSH_PRIVATE_KEY` - SSH private key (complete key)

### Step 3: Deploy

```bash
# Push to main branch
git push origin main

# GitHub Actions automatically:
# - Runs tests
# - Deploys to server
# - Runs migrations
# - Clears caches
# - Restarts services
```

Monitor deployment in GitHub Actions tab.

### Step 4: Verify Deployment

```bash
# Check application is up
curl https://yourdomain.com/health

# Check logs
ssh user@server
cd /var/www/nextgenbeing
tail -f storage/logs/laravel.log
```

---

## 📚 Additional Resources

### Important Documentation Files
- **[README.md](README.md)** - Project overview
- **[OAUTH_SETUP_GUIDE.md](OAUTH_SETUP_GUIDE.md)** - Detailed OAuth setup
- **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Production deployment guide
- **[INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)** - Detailed installation steps

### Key Files to Know

**Models:**
- `app/Models/User.php` - User with social accounts
- `app/Models/SocialAccount.php` - OAuth provider links
- `app/Models/LearningPath.php` - AI learning paths
- `app/Models/Post.php` - Blog posts
- `app/Models/Achievement.php` - User achievements

**Services:**
- `app/Services/Auth/SocialAuthService.php` - OAuth logic
- `app/Services/AI/AIRecommendationEngine.php` - Recommendations
- `app/Services/AI/AILearningPlanGenerator.php` - Learning paths
- `app/Services/AI/AIInsightsService.php` - Analytics

**Routes:**
- `/auth/oauth/{provider}/redirect` - OAuth login
- `/auth/oauth/{provider}/callback` - OAuth callback
- `/auth/oauth/{provider}/disconnect` - Disconnect account

**Views:**
- `resources/views/components/social-auth-buttons.blade.php` - Login buttons
- `resources/views/components/social-account-manager.blade.php` - Account manager
- `resources/views/components/ai-*.blade.php` - AI components

---

## 🐛 Troubleshooting

### OAuth Login Not Working
1. Verify OAuth credentials in `.env`
2. Check redirect URIs match exactly
3. Check `social_accounts` table exists (run migrations)
4. View logs: `tail -f storage/logs/laravel.log`

### Database Connection Error
1. Verify PostgreSQL is running: `sudo systemctl status postgresql`
2. Check credentials in `.env`
3. Test connection: `php artisan tinker` → `DB::connection()->getPdo()`

### Assets Not Loading
1. Run build: `npm run build`
2. Check public/build directory exists
3. Clear cache: `php artisan cache:clear`

### Deployment Failed
1. Check GitHub Actions logs
2. SSH to server and check: `storage/logs/laravel.log`
3. Verify all secrets are set in GitHub
4. Ensure server has proper PHP version and extensions

---

## ✅ Quick Checklist

- [ ] Clone repository
- [ ] Install dependencies (`composer install`, `npm install`)
- [ ] Setup `.env` file
- [ ] Create database
- [ ] Run migrations
- [ ] Setup OAuth providers (Google, GitHub, Discord)
- [ ] Add OAuth credentials to `.env`
- [ ] Update login page with `<x-social-auth-buttons />`
- [ ] Update settings page with `<x-social-account-manager />`
- [ ] Test local setup (`php artisan serve`)
- [ ] (Production) Setup server per DEPLOYMENT_GUIDE.md
- [ ] (Production) Add GitHub secrets
- [ ] (Production) Push to main branch to trigger deployment

---

## 🆘 Getting Help

1. Check the relevant guide (OAUTH_SETUP_GUIDE.md, DEPLOYMENT_GUIDE.md)
2. Check application logs: `storage/logs/laravel.log`
3. Check server logs (production): SSH to server
4. Review error messages in browser console
5. Check GitHub Actions logs for deployment issues

---

**Last Updated:** November 8, 2024
**Version:** 1.0
**Status:** Production Ready ✨
