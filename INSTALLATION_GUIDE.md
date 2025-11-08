# NextGen Being - Installation & Setup Guide

Complete installation guide for the Video Generation and Social Media Auto-Publishing system.

---

## Prerequisites

- PHP 8.2 or higher
- Composer
- PostgreSQL 15 or higher
- Redis (for queues and caching)
- FFmpeg (for video processing)
- Node.js & NPM (for frontend assets)

---

## Step 1: Clone & Install Dependencies

```bash
# Clone repository (if not already cloned)
cd d:/projects/MyProjects/next-gen-being

# Install PHP dependencies
composer require laravel/socialite
composer require socialiteproviders/youtube
composer require socialiteproviders/instagram
composer require socialiteproviders/linkedin-openid

# Install frontend dependencies
npm install
npm run build
```

---

## Step 2: Environment Configuration

### Copy Environment File

```bash
cp .env.example .env
```

### Configure Database

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nextgenbeing
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### Configure Redis

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
```

### Configure Core Services

```env
APP_NAME="NextGen Being"
APP_ENV=local
APP_KEY=base64:your_key_here
APP_DEBUG=true
APP_URL=http://localhost:9070

# Generate application key
php artisan key:generate
```

---

## Step 3: AI Configuration

### Option A: Groq (Recommended - FREE & FAST)

```env
AI_PROVIDER=groq
GROQ_API_KEY=gsk_your_groq_key_here
GROQ_MODEL=llama-3.3-70b-versatile
```

Get your Groq API key at: https://console.groq.com/keys

### Option B: OpenAI (Paid)

```env
AI_PROVIDER=openai
OPENAI_API_KEY=sk-your_openai_key_here
OPENAI_MODEL=gpt-4-turbo-preview
OPENAI_ORGANIZATION=org-your_org_id

# Text-to-Speech configuration
OPENAI_TTS_MODEL=tts-1-hd
OPENAI_TTS_VOICE=onyx
```

Get your OpenAI API key at: https://platform.openai.com/api-keys

---

## Step 4: Video Generation Services

### Pexels API (Required - FREE)

```env
PEXELS_API_KEY=your_pexels_key_here
```

Get your key at: https://www.pexels.com/api/

### ElevenLabs (Optional - Premium TTS)

```env
ELEVENLABS_API_KEY=your_elevenlabs_key_here
ELEVENLABS_VOICE_ID=21m00Tcm4TlvDq8ikWAM
ELEVENLABS_MODEL=eleven_multilingual_v2
```

Get your key at: https://elevenlabs.io/

---

## Step 5: Social Media OAuth Configuration

### YouTube (Google OAuth)

**Setup Steps:**
1. Go to https://console.cloud.google.com/
2. Create new project or select existing
3. Enable "YouTube Data API v3"
4. Go to "Credentials" → "Create Credentials" → "OAuth client ID"
5. Application type: "Web application"
6. Add authorized redirect URI: `http://localhost:9070/auth/youtube/callback`
7. Copy Client ID and Client Secret

```env
YOUTUBE_CLIENT_ID=your_google_client_id.apps.googleusercontent.com
YOUTUBE_CLIENT_SECRET=your_google_client_secret
YOUTUBE_REDIRECT_URI=${APP_URL}/auth/youtube/callback
```

**Important Notes:**
- Free quota: 10,000 units/day (~6 video uploads)
- Request quota increase for production use
- Scopes required: `youtube.upload`, `youtube.readonly`

---

### Instagram (Facebook/Meta OAuth)

**Setup Steps:**
1. Go to https://developers.facebook.com/
2. Create new app → "Business" type
3. Add "Instagram Basic Display" product
4. Add "Instagram Graph API" product
5. Add redirect URI: `http://localhost:9070/auth/instagram/callback`
6. Copy App ID and App Secret

```env
INSTAGRAM_CLIENT_ID=your_facebook_app_id
INSTAGRAM_CLIENT_SECRET=your_facebook_app_secret
INSTAGRAM_REDIRECT_URI=${APP_URL}/auth/instagram/callback
```

**Requirements:**
- Must have Facebook Business Page
- Instagram account must be Business or Creator account
- Instagram must be connected to Facebook Page
- Video requirements: MP4, 9:16 aspect ratio, 3-60 seconds

---

### Facebook

**Setup Steps:**
1. Same Facebook app as Instagram
2. Add "Facebook Login" product
3. Add redirect URI: `http://localhost:9070/auth/facebook/callback`

```env
FACEBOOK_CLIENT_ID=your_facebook_app_id
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
FACEBOOK_REDIRECT_URI=${APP_URL}/auth/facebook/callback
```

---

### Twitter / X

**Setup Steps:**
1. Go to https://developer.twitter.com/
2. Create new app
3. Enable OAuth 2.0
4. Request "Elevated" access (required for media upload)
5. Add callback URL: `http://localhost:9070/auth/twitter/callback`
6. Copy API Key and API Secret

```env
TWITTER_CLIENT_ID=your_twitter_api_key
TWITTER_CLIENT_SECRET=your_twitter_api_secret
TWITTER_REDIRECT_URI=${APP_URL}/auth/twitter/callback
```

**API Costs:**
- Free tier: Very limited, not suitable for production
- Basic: $100/month (10,000 tweets)
- Pro: $5,000/month (1M tweets)

---

### LinkedIn

**Setup Steps:**
1. Go to https://www.linkedin.com/developers/
2. Create new app
3. Add "Sign In with LinkedIn" product
4. Add redirect URL: `http://localhost:9070/auth/linkedin/callback`
5. Copy Client ID and Client Secret

```env
LINKEDIN_CLIENT_ID=your_linkedin_client_id
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret
LINKEDIN_REDIRECT_URI=${APP_URL}/auth/linkedin/callback
```

---

### Telegram Bot (Recommended - FREE & Simple)

**Setup Steps:**
1. Open Telegram and search for `@BotFather`
2. Send `/newbot` command
3. Follow prompts to create bot
4. Copy bot token
5. Create your channel
6. Add bot as administrator to channel
7. Get channel ID (use `@userinfobot` in channel)

```env
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_CHANNEL_ID=@yourchannel
# Or numeric ID: -1001234567890
```

**Advantages:**
- No OAuth required
- No rate limits
- Completely free
- Easiest to set up
- Best for official NextGen Being channel

---

## Step 6: Storage Configuration

### Option A: Local Storage (Development)

```env
FILESYSTEM_DISK=local
```

### Option B: AWS S3 (Production)

```env
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=nextgenbeing-videos
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Option C: Cloudflare R2 (Recommended - Cheaper)

```env
FILESYSTEM_DISK=r2

# Use AWS S3 credentials but with R2 endpoint
AWS_ACCESS_KEY_ID=your_r2_access_key
AWS_SECRET_ACCESS_KEY=your_r2_secret_key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=nextgenbeing-videos
AWS_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

---

## Step 7: Database Setup

```bash
# Create database
createdb nextgenbeing

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

---

## Step 8: Install FFmpeg

### Ubuntu/Debian

```bash
sudo apt update
sudo apt install ffmpeg
ffmpeg -version
```

### macOS

```bash
brew install ffmpeg
ffmpeg -version
```

### Windows

1. Download from https://ffmpeg.org/download.html
2. Extract to `C:\ffmpeg`
3. Add `C:\ffmpeg\bin` to PATH
4. Verify: `ffmpeg -version`

---

## Step 9: Register Service Providers

Add to `config/app.php`:

```php
'providers' => [
    // ...existing providers
    App\Providers\SocialiteServiceProvider::class,
],
```

Or if using Laravel 11's auto-discovery, add to `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\SocialiteServiceProvider::class,
];
```

---

## Step 10: Configure Cron Jobs

### Add to Crontab

```bash
crontab -e
```

Add this line:

```
* * * * * cd /path/to/nextgenbeing && php artisan schedule:run >> /dev/null 2>&1
```

### Configure Schedule

Edit `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Auto-publish videos to social media every hour
    $schedule->command('social:auto-publish')
        ->hourly()
        ->withoutOverlapping()
        ->runInBackground();

    // Update engagement metrics daily at 2 AM
    $schedule->command('social:update-engagement')
        ->dailyAt('02:00')
        ->withoutOverlapping();

    // Clean up old temporary files daily
    $schedule->command('app:cleanup-temp-files')
        ->daily();
}
```

---

## Step 11: Start Services

### Development Environment

```bash
# Start Laravel development server
php artisan serve --port=9070

# Start queue worker (in separate terminal)
php artisan queue:work --queue=default,video,social --tries=3

# Start Vite dev server (in separate terminal)
npm run dev
```

### Production Environment (Using Supervisor)

Create `/etc/supervisor/conf.d/nextgenbeing-worker.conf`:

```ini
[program:nextgenbeing-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nextgenbeing/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/nextgenbeing/storage/logs/worker.log
stopwaitsecs=3600
```

Reload supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start nextgenbeing-worker:*
```

---

## Step 12: Verify Installation

### Test FFmpeg

```bash
php artisan tinker

$service = app(\App\Services\Video\VideoEditorService::class);
$service->checkFFmpegInstalled(); // Should return true
$service->getFFmpegVersion(); // Should return version string
```

### Test Video Generation

```bash
# Create a test post first via Filament admin
# Then generate video:
php artisan video:generate 1 tiktok --dry-run

# If successful, try actual generation:
php artisan video:generate 1 tiktok
```

### Test Social Media Publishing

```bash
# Dry run to see what would be published:
php artisan social:auto-publish --dry-run

# Connect social accounts via Filament first:
# Navigate to: http://localhost:9070/admin/social-media-accounts
# Click "Connect Platform" and authorize each platform

# Then publish:
php artisan social:auto-publish
```

---

## Step 13: Access Admin Panel

### Create Admin User

```bash
php artisan tinker

$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@nextgenbeing.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);

# Assign admin role if you have roles table
$user->roles()->attach(\App\Models\Role::where('slug', 'admin')->first());
```

### Access Filament

Navigate to:
```
http://localhost:9070/admin
```

Login with admin credentials.

---

## Step 14: Production Deployment Checklist

### Security

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Generate new `APP_KEY`
- [ ] Use HTTPS (configure SSL certificate)
- [ ] Set secure session/cookie settings
- [ ] Enable CSRF protection
- [ ] Configure CORS properly
- [ ] Use environment variables for all secrets
- [ ] Enable rate limiting on API routes

### Performance

- [ ] Enable OPcache
- [ ] Configure Redis for cache and sessions
- [ ] Set up queue workers with Supervisor
- [ ] Optimize Composer autoloader: `composer install --optimize-autoloader --no-dev`
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`
- [ ] Build frontend assets: `npm run build`

### Monitoring

- [ ] Set up application monitoring (Sentry, Bugsnag)
- [ ] Configure logging (daily, slack, etc.)
- [ ] Set up uptime monitoring
- [ ] Monitor queue health
- [ ] Monitor disk space (videos can be large)
- [ ] Monitor API quotas (YouTube, Twitter)

### Backup

- [ ] Database backups (daily)
- [ ] File storage backups
- [ ] Environment file backup (securely)
- [ ] Test restore procedures

---

## Common Issues & Solutions

### Issue: FFmpeg not found

**Error**: `sh: ffmpeg: command not found`

**Solution**:
```bash
# Check if FFmpeg is installed
which ffmpeg

# If not found, install it
sudo apt install ffmpeg  # Ubuntu
brew install ffmpeg      # macOS
```

### Issue: OAuth callback 404

**Error**: Callback URL returns 404

**Solution**:
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Verify routes exist
php artisan route:list | grep social.auth
```

### Issue: Encrypted token error

**Error**: `The payload is invalid`

**Solution**:
```bash
# Make sure APP_KEY is set
php artisan key:generate

# Reconnect social accounts
```

### Issue: YouTube quota exceeded

**Error**: `quotaExceeded`

**Solution**:
- Request quota increase from Google
- Reduce video upload frequency
- Use multiple YouTube accounts

### Issue: Video upload fails

**Error**: Various upload errors

**Solution**:
```bash
# Check disk space
df -h

# Check storage permissions
chmod -R 775 storage
chown -R www-data:www-data storage

# Check video file exists and is accessible
ls -la storage/app/public/videos/
```

### Issue: Queue not processing

**Error**: Jobs stuck in queue

**Solution**:
```bash
# Check queue worker is running
php artisan queue:work --once

# Check Redis connection
redis-cli ping

# Restart queue workers
sudo supervisorctl restart nextgenbeing-worker:*
```

---

## Quick Reference Commands

### Video Generation

```bash
# Generate video from post
php artisan video:generate {post_id} {type}

# Available types: youtube, tiktok, reel, short
php artisan video:generate 1 tiktok

# With verbose output
php artisan video:generate 1 youtube -v
```

### Social Media Publishing

```bash
# Auto-publish ready videos
php artisan social:auto-publish

# Publish specific post
php artisan social:auto-publish --post_id=123

# Dry run (preview only)
php artisan social:auto-publish --dry-run
```

### Engagement Metrics

```bash
# Update all recent posts
php artisan social:update-engagement

# Update specific post
php artisan social:update-engagement --post_id=123

# Update last 30 days
php artisan social:update-engagement --days=30
```

### Maintenance

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan optimize

# Check queue status
php artisan queue:monitor

# Retry failed jobs
php artisan queue:retry all
```

---

## Support & Resources

- **Documentation**: `/docs` folder in this repository
- **Phase Summaries**: See `PHASE_*_COMPLETION_SUMMARY.md` files
- **Implementation Roadmap**: `IMPLEMENTATION_ROADMAP.md`
- **GitHub Issues**: https://github.com/anthropics/claude-code/issues

---

## Next Steps

After successful installation:

1. **Generate your first video**:
   ```bash
   php artisan video:generate 1 tiktok
   ```

2. **Connect social media accounts**:
   - Visit: `http://localhost:9070/admin/social-media-accounts`
   - Connect platforms one by one

3. **Auto-publish**:
   ```bash
   php artisan social:auto-publish
   ```

4. **Monitor engagement**:
   ```bash
   php artisan social:update-engagement
   ```

5. **Set up cron jobs** for automated publishing

6. **Consider Phase 4** implementation for queue-based processing

---

**Installation Complete!** 🎉

Your NextGen Being video generation and social media auto-publishing system is ready to use.
