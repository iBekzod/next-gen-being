# NextGen Being - Dependencies & Package Installation

Complete list of required dependencies and installation instructions.

---

## Composer Dependencies

### Core Laravel Packages (Already Installed)

```json
{
    "laravel/framework": "^11.0",
    "laravel/sanctum": "^4.0",
    "laravel/tinker": "^2.9",
    "filament/filament": "^3.0"
}
```

### Required New Packages

Run these commands to install all required packages:

```bash
# Laravel Socialite (OAuth integration)
composer require laravel/socialite

# Socialite Providers (platform-specific OAuth)
composer require socialiteproviders/youtube
composer require socialiteproviders/instagram
composer require socialiteproviders/linkedin-openid

# Optional: Additional providers if needed later
# composer require socialiteproviders/facebook
# composer require socialiteproviders/twitter-oauth-2
```

### Complete Composer Require Command

```bash
composer require \
    laravel/socialite \
    socialiteproviders/youtube \
    socialiteproviders/instagram \
    socialiteproviders/linkedin-openid
```

---

## System Requirements

### PHP Extensions

Required PHP extensions (usually included in PHP installation):

```bash
# Check installed extensions
php -m

# Required extensions:
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- BCMath PHP Extension
- Fileinfo PHP Extension
- GD PHP Extension (for image manipulation)
```

### FFmpeg

**Required for video processing**

#### Ubuntu/Debian
```bash
sudo apt update
sudo apt install ffmpeg

# Verify installation
ffmpeg -version
```

#### macOS
```bash
brew install ffmpeg

# Verify installation
ffmpeg -version
```

#### Windows
1. Download from https://ffmpeg.org/download.html
2. Extract to `C:\ffmpeg`
3. Add `C:\ffmpeg\bin` to system PATH
4. Open new terminal and verify:
```bash
ffmpeg -version
```

---

## Database

### PostgreSQL

#### Ubuntu/Debian
```bash
sudo apt update
sudo apt install postgresql postgresql-contrib

# Start PostgreSQL
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Create database
sudo -u postgres createdb nextgenbeing
```

#### macOS
```bash
brew install postgresql@15

# Start PostgreSQL
brew services start postgresql@15

# Create database
createdb nextgenbeing
```

#### Windows
1. Download installer from https://www.postgresql.org/download/windows/
2. Run installer
3. Use pgAdmin to create database

---

## Redis

### Ubuntu/Debian
```bash
sudo apt update
sudo apt install redis-server

# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test connection
redis-cli ping
# Should return: PONG
```

### macOS
```bash
brew install redis

# Start Redis
brew services start redis

# Test connection
redis-cli ping
```

### Windows
1. Download Redis for Windows from https://github.com/microsoftarchive/redis/releases
2. Install and start service
3. Test connection: `redis-cli ping`

---

## Node.js & NPM

### Ubuntu/Debian
```bash
# Install Node.js 20.x LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs

# Verify
node -v
npm -v
```

### macOS
```bash
brew install node

# Verify
node -v
npm -v
```

### Windows
1. Download installer from https://nodejs.org/
2. Run installer
3. Verify in new terminal:
```bash
node -v
npm -v
```

---

## Frontend Dependencies

### Install NPM Packages

```bash
cd /path/to/nextgenbeing

# Install dependencies
npm install

# Build for development
npm run dev

# Build for production
npm run build
```

---

## Configuration Files

### 1. Register Service Provider

#### For Laravel 11 (Auto-Discovery)

Add to `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\SocialiteServiceProvider::class,  // Add this line
];
```

#### For Laravel 10 and below

Add to `config/app.php`:

```php
'providers' => [
    // ... existing providers
    App\Providers\SocialiteServiceProvider::class,
],
```

---

### 2. Configure Socialite Providers

The service provider (`App\Providers\SocialiteServiceProvider`) has been created. It configures:

- YouTube (via Google OAuth)
- Instagram
- LinkedIn OpenID Connect
- Twitter OAuth 2.0
- Facebook

No additional configuration needed - just set environment variables.

---

## API Keys Setup

### 1. OpenAI API (Required)

**Purpose**: Script generation (GPT-4) and voiceover (TTS)

**Get Key**: https://platform.openai.com/api-keys

```env
OPENAI_API_KEY=sk-proj-...
OPENAI_ORGANIZATION=org-...
OPENAI_MODEL=gpt-4-turbo-preview
OPENAI_TTS_MODEL=tts-1-hd
OPENAI_TTS_VOICE=onyx
```

**Cost Estimate**:
- GPT-4 Turbo: $0.01/1K tokens (~$0.02 per script)
- TTS-1-HD: $15/1M characters (~$0.015 per minute)

---

### 2. Pexels API (Required)

**Purpose**: Free stock video footage

**Get Key**: https://www.pexels.com/api/

```env
PEXELS_API_KEY=your_pexels_key
```

**Cost**: FREE forever!

---

### 3. ElevenLabs (Optional - Video Pro Tier Only)

**Purpose**: Premium quality voiceovers

**Get Key**: https://elevenlabs.io/

```env
ELEVENLABS_API_KEY=your_elevenlabs_key
ELEVENLABS_VOICE_ID=21m00Tcm4TlvDq8ikWAM
ELEVENLABS_MODEL=eleven_multilingual_v2
```

**Cost**: $0.30 per 1K characters

---

### 4. YouTube Data API v3 (Optional)

**Purpose**: Upload videos to YouTube

**Setup**:
1. https://console.cloud.google.com/
2. Create project
3. Enable YouTube Data API v3
4. Create OAuth 2.0 credentials

```env
YOUTUBE_CLIENT_ID=your_id.apps.googleusercontent.com
YOUTUBE_CLIENT_SECRET=your_secret
YOUTUBE_REDIRECT_URI=${APP_URL}/auth/youtube/callback
```

**Quota**: 10,000 units/day (free)

---

### 5. Instagram Graph API (Optional)

**Purpose**: Post Reels to Instagram

**Setup**:
1. https://developers.facebook.com/
2. Create Facebook app
3. Add Instagram products

```env
INSTAGRAM_CLIENT_ID=your_facebook_app_id
INSTAGRAM_CLIENT_SECRET=your_facebook_app_secret
INSTAGRAM_REDIRECT_URI=${APP_URL}/auth/instagram/callback
```

**Cost**: FREE

---

### 6. Twitter API v2 (Optional)

**Purpose**: Post videos to Twitter/X

**Setup**:
1. https://developer.twitter.com/
2. Request elevated access

```env
TWITTER_CLIENT_ID=your_api_key
TWITTER_CLIENT_SECRET=your_api_secret
TWITTER_REDIRECT_URI=${APP_URL}/auth/twitter/callback
```

**Cost**: $100/month for Basic (required for media upload)

---

### 7. Telegram Bot (Recommended - FREE)

**Purpose**: Post to Telegram channel

**Setup**:
1. Talk to @BotFather on Telegram
2. Create bot: `/newbot`
3. Add bot as admin to channel

```env
TELEGRAM_BOT_TOKEN=123456:ABC-DEF...
TELEGRAM_CHANNEL_ID=@yourchannel
```

**Cost**: FREE forever, no limits!

---

## Storage Configuration

### Option 1: Local Storage (Development)

```env
FILESYSTEM_DISK=local
```

No additional setup needed.

---

### Option 2: AWS S3 (Production)

**Setup**:
1. Create S3 bucket
2. Create IAM user with S3 permissions
3. Generate access key

```env
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=nextgenbeing-videos
AWS_USE_PATH_STYLE_ENDPOINT=false
```

**Cost**: ~$0.023/GB/month + transfer costs

---

### Option 3: Cloudflare R2 (Recommended)

**Setup**:
1. Create R2 bucket at https://dash.cloudflare.com/
2. Generate API token

```env
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=your_r2_access_key
AWS_SECRET_ACCESS_KEY=your_r2_secret_key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=nextgenbeing-videos
AWS_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

**Cost**: $0.015/GB/month, FREE egress!

---

## Production Server Setup

### Web Server

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name nextgenbeing.com;
    root /var/www/nextgenbeing/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

### PHP-FPM Configuration

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

---

### Supervisor (Queue Workers)

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

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start nextgenbeing-worker:*
```

---

### Cron Jobs

Add to crontab:
```bash
crontab -e

# Add this line:
* * * * * cd /var/www/nextgenbeing && php artisan schedule:run >> /dev/null 2>&1
```

---

## Verification Commands

### Test Database Connection

```bash
php artisan tinker

DB::connection()->getPdo();
// Should not throw error
```

### Test Redis Connection

```bash
php artisan tinker

Redis::connection()->ping();
// Should return "+PONG"
```

### Test FFmpeg

```bash
php artisan tinker

$service = app(\App\Services\Video\VideoEditorService::class);
$service->checkFFmpegInstalled(); // Should return true
```

### Test Queue

```bash
# Dispatch test job
php artisan tinker

dispatch(function () {
    \Log::info('Test job executed');
});

# Check logs
tail -f storage/logs/laravel.log
```

---

## Troubleshooting

### Composer Install Fails

**Error**: Memory limit exhausted

**Solution**:
```bash
php -d memory_limit=-1 /usr/local/bin/composer install
```

### FFmpeg Not Found

**Error**: `sh: ffmpeg: command not found`

**Solution**:
```bash
# Find FFmpeg location
which ffmpeg

# Add to PATH or create symlink
sudo ln -s /usr/local/bin/ffmpeg /usr/bin/ffmpeg
```

### Queue Not Processing

**Error**: Jobs stuck in queue

**Solution**:
```bash
# Check worker is running
ps aux | grep "queue:work"

# Restart workers
sudo supervisorctl restart nextgenbeing-worker:*

# Or manually
php artisan queue:restart
```

### Permission Errors

**Error**: Permission denied on storage

**Solution**:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Next Steps

After installing all dependencies:

1. **Run migrations**:
   ```bash
   php artisan migrate
   ```

2. **Configure API keys** in `.env`

3. **Connect social media accounts** via Filament

4. **Generate first video**:
   ```bash
   php artisan video:generate 1 tiktok
   ```

5. **Test auto-publishing**:
   ```bash
   php artisan social:auto-publish --dry-run
   ```

---

## Reference

- **Laravel Socialite**: https://laravel.com/docs/11.x/socialite
- **Socialite Providers**: https://socialiteproviders.com/
- **FFmpeg**: https://ffmpeg.org/documentation.html
- **Redis**: https://redis.io/documentation
- **PostgreSQL**: https://www.postgresql.org/docs/

---

**All dependencies documented!** ✅

See [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md) for complete setup instructions.
