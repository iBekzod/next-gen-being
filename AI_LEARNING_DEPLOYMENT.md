# AI Learning & Tutorials Platform - Complete Deployment Guide

## Quick Start (5 minutes)

If you just want to test the system quickly:

```bash
# 1. Start database (if using Docker)
docker-compose up -d

# 2. Run migrations
php artisan migrate --step

# 3. Seed categories and tags
php artisan db:seed --class=AILearningCategoriesAndTagsSeeder

# 4. Create sample products
php artisan ai-learning:create-samples --count=10

# 5. Start the application
php artisan serve --port=9070

# 6. In another terminal, start the scheduler
php artisan schedule:work
```

Then visit:
- Marketplace: http://localhost:9070/resources
- Admin: http://localhost:9070/admin
- Dashboard: http://localhost:9070/admin/dashboard

---

## 🏗️ Architecture Overview

```
AI Learning Platform
├── Content Generation (Automated)
│   ├── Weekly Tutorials (8-part series)
│   ├── Monthly Prompts (10 templates)
│   └── Daily SEO Optimization
│
├── Digital Marketplace
│   ├── Product Browsing (/resources)
│   ├── Purchase Flow (LemonSqueezy)
│   └── Download Management
│
├── Monetization
│   ├── Subscription Tiers (via existing system)
│   ├── Digital Product Sales (new)
│   ├── Revenue Tracking (BloggerEarning)
│   └── Creator Payouts
│
└── Admin Dashboard (Filament)
    ├── Product Management
    ├── Purchase Tracking
    ├── Earnings Report
    └── Content Scheduling
```

---

## 📦 New System Components

### Database Tables

**digital_products**
- Title, slug, description, type (prompt/template/tutorial/course/cheatsheet/code_example)
- Pricing (price, original_price, is_free)
- Access control (tier_required: free/basic/pro/team)
- Files (file_path, preview_file_path, thumbnail)
- Metadata (tags, category, features, includes)
- Publishing (status, published_at)
- Stats (downloads_count, purchases_count, rating, reviews_count)

**product_purchases**
- User, product, amount, currency, status
- License key with 10-download limit
- Creator/platform revenue split
- LemonSqueezy integration (order_id, receipt_url)

### Console Commands

```bash
# Generate weekly tutorials
php artisan ai-learning:generate-weekly {--day=} {--dry-run}

# Generate monthly prompts
php artisan ai-learning:generate-prompts {--count=5}

# Create sample products
php artisan ai-learning:create-samples {--count=10}

# Seed categories
php artisan db:seed --class=AILearningCategoriesAndTagsSeeder
```

### API Endpoints

```
GET    /resources                      # Product listing
GET    /resources/{product:slug}       # Product details
POST   /resources/{product}/purchase   # Initiate purchase
GET    /resources/downloads            # Download center
GET    /resources/my-purchases         # Purchase history
GET    /resources/purchases/{purchase}/download
```

### Admin Resources

**Digital Products** in Admin Panel
- Full CRUD for products
- File upload (private disk)
- Publishing workflow
- Revenue tracking

---

## 🚀 Complete Deployment (Production)

### 1. Server Setup

```bash
# Install PHP extensions (if not already present)
sudo apt-get install php8.2-pgsql php8.2-gd php8.2-curl php8.2-mbstring

# Create app directory
sudo mkdir -p /var/www/nextgenbeing
sudo chown -R www-data:www-data /var/www/nextgenbeing

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Application Setup

```bash
cd /var/www/nextgenbeing

# Clone/extract code
# ...

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Set permissions
chmod -R 775 storage bootstrap/cache
chmod -R 775 storage/app/private
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate

# Configure database
nano .env
# Update DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

**Essential .env settings:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=nextgenbeing
DB_USERNAME=ng_user
DB_PASSWORD=strong_password

# Storage (use S3 for production)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=nextgenbeing-files

# LemonSqueezy
LEMONSQUEEZY_API_KEY=...
LEMONSQUEEZY_SIGNING_SECRET=...

# AI Providers (at least one required)
OPENAI_API_KEY=sk-...          # For tutorials (optional)
GROQ_API_KEY=gsk-...           # For prompts (recommended)

# Automation
AI_LEARNING_ENABLED=true
TUTORIAL_GENERATION_ENABLED=true
PROMPT_LIBRARY_ENABLED=true
```

### 4. Database Setup

```bash
php artisan migrate --force
php artisan db:seed --class=AILearningCategoriesAndTagsSeeder
php artisan ai-learning:create-samples --count=20
```

### 5. Scheduler Setup

**Option A: Cron Job (Simple)**

```bash
# Edit crontab
crontab -e

# Add this line:
* * * * * cd /var/www/nextgenbeing && php artisan schedule:run >> /dev/null 2>&1
```

**Option B: Supervisor (Recommended)**

```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/laravel-scheduler.conf
```

```ini
[program:laravel-scheduler]
process_name=%(program_name)s
command=php /var/www/nextgenbeing/artisan schedule:work
autostart=true
autorestart=true
numprocs=1
startsecs=0
stopwaitsecs=3600
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/laravel-scheduler.log
stopasgroup=true
killasgroup=true
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-scheduler
```

### 6. Web Server Configuration

**Nginx vhost:**

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    root /var/www/nextgenbeing/public;
    index index.php index.html;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

### 7. SSL Certificate

```bash
# Using Let's Encrypt (free)
sudo apt-get install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d yourdomain.com

# Auto-renew
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

### 8. Queue Setup (Optional, for async processing)

```bash
# Create queue worker supervisor config
sudo nano /etc/supervisor/conf.d/laravel-queue.conf
```

```ini
[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nextgenbeing/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/laravel-queue.log
```

---

## 🔍 Verification Checklist

### Database
- [ ] Migrations completed successfully
- [ ] `digital_products` table exists
- [ ] `product_purchases` table exists
- [ ] Categories seeded (8 categories)
- [ ] Tags seeded (35+ tags)

### Application
- [ ] Marketplace loads: `https://yourdomain.com/resources`
- [ ] Products visible in grid
- [ ] Filters work (type, category, sort)
- [ ] Product detail page loads
- [ ] "View" button navigates correctly

### Admin
- [ ] Admin panel accessible: `https://yourdomain.com/admin`
- [ ] Can see "Digital Products" section
- [ ] Can create new product
- [ ] Can edit existing product
- [ ] Can upload files

### Automation
- [ ] Scheduler running: `sudo supervisorctl status laravel-scheduler`
- [ ] No errors in `/var/log/laravel-scheduler.log`
- [ ] Cron job exists: `crontab -l`
- [ ] Test tutorial generation:
  ```bash
  php artisan ai-learning:generate-weekly --day=Monday --dry-run
  ```

### Purchases & Payments
- [ ] LemonSqueezy webhook configured
- [ ] Webhook URL publicly accessible
- [ ] Test purchase (LemonSqueezy test mode)
- [ ] Purchase creates `product_purchases` record
- [ ] Creator earning tracked in `blogger_earnings`
- [ ] Download link works

---

## 📊 Monitoring & Maintenance

### Daily Tasks
- [ ] Check error logs: `tail -f /var/log/laravel-scheduler.log`
- [ ] Monitor disk space: `df -h`
- [ ] Check database size: `sudo -u postgres psql -c "SELECT pg_database.datname, pg_size_pretty(pg_database_size(pg_database.datname)) FROM pg_database;"`

### Weekly Tasks
- [ ] Check generated content count
- [ ] Verify prompts created: `php artisan tinker` → `DigitalProduct::where('type', 'prompt')->whereDate('published_at', '>=', now()->subWeek())->count()`
- [ ] Check purchase volume
- [ ] Review error logs

### Monthly Tasks
- [ ] Database backup (set up automated backups)
- [ ] Update dependencies: `composer update`
- [ ] Review scheduler performance
- [ ] Check revenue totals
- [ ] Analyze popular products

### Useful Commands

```bash
# View current schedule
php artisan schedule:list

# Test command
php artisan ai-learning:generate-weekly --day=Monday --dry-run

# Generate real content
php artisan ai-learning:generate-weekly --day=Monday

# Check database
php artisan tinker
>>> Post::whereDate('published_at', today())->count()
>>> DigitalProduct::published()->count()
>>> ProductPurchase::where('status', 'completed')->count()

# View logs
tail -f /var/log/laravel-scheduler.log
tail -f /var/log/laravel-app.log
tail -f /var/log/laravel-queue.log
```

---

## 🐛 Troubleshooting

### Scheduler Not Running

```bash
# Check if supervisor process is running
sudo supervisorctl status laravel-scheduler

# Restart if needed
sudo supervisorctl restart laravel-scheduler

# Check logs
sudo tail -f /var/log/laravel-scheduler.log
```

### AI Generation Fails

```bash
# Test generation manually
php artisan ai-learning:generate-weekly --day=Monday

# Check API keys in .env
echo $OPENAI_API_KEY
echo $GROQ_API_KEY

# Test API connection
php artisan tinker
>>> app(AIService::class)->testConnection()
```

### LemonSqueezy Webhook Issues

```bash
# Check webhook was received
grep "webhook" /var/log/laravel-app.log

# Verify signing secret
grep LEMONSQUEEZY_SIGNING_SECRET .env

# Test webhook manually (LemonSqueezy dashboard)
```

### Products Not Appearing

```bash
php artisan tinker
>>> DigitalProduct::count()  # Should be > 0
>>> DigitalProduct::published()->count()  # Should match above
>>> DigitalProduct::first()->toArray()  # Verify status = 'published'
```

### File Uploads Fail

```bash
# Check permissions
ls -la storage/app/private/
sudo chmod -R 775 storage/app/private

# Check disk space
df -h

# Test S3 connectivity
php artisan tinker
>>> Storage::disk('s3')->put('test.txt', 'test')
```

---

## 🔐 Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] All sensitive data in `.env` (not in code)
- [ ] Database password is strong
- [ ] SSH keys for deployment configured
- [ ] SSL/HTTPS enabled
- [ ] Firewall rules in place
- [ ] Regular backups configured
- [ ] LemonSqueezy webhook signing secret set
- [ ] S3 bucket not publicly accessible
- [ ] PHP files not web-accessible outside `/public`

---

## 📈 Performance Tuning

### Database
```sql
-- Create indexes
CREATE INDEX idx_digital_products_status_published ON digital_products(status, published_at DESC);
CREATE INDEX idx_product_purchases_user_status ON product_purchases(user_id, status);
CREATE INDEX idx_posts_series_slug ON posts(series_slug);
```

### PHP
```ini
; php.ini
max_execution_time = 300
memory_limit = 256M
upload_max_filesize = 100M
post_max_size = 100M
opcache.enable = 1
```

### Nginx
```nginx
# Gzip compression
gzip on;
gzip_types text/plain text/css text/javascript application/json;
gzip_min_length 1000;

# Caching
add_header Cache-Control "public, max-age=3600";
```

---

## 🎯 Success Indicators

✅ Everything working when:
1. Products list visible at `/resources`
2. Can view individual product details
3. Admin panel accessible at `/admin`
4. Tutorials generated automatically (check logs)
5. Prompts created monthly
6. Can purchase and download
7. Creator earnings tracked
8. No errors in logs

---

## 📚 Related Documentation

- [AI Platform Implementation Summary](AI_PLATFORM_IMPLEMENTATION_SUMMARY.md)
- [Setup Guide](SETUP_AI_LEARNING_PLATFORM.md)
- [Quick Start Script](QUICK_START.sh)

---

## 🚀 Launch Checklist

Before going live:

- [ ] Database backed up
- [ ] All migrations run successfully
- [ ] Sample content created
- [ ] Scheduler tested and running
- [ ] LemonSqueezy configured
- [ ] Email configured (for notifications)
- [ ] SSL certificate installed
- [ ] Domain DNS configured
- [ ] Analytics configured (if desired)
- [ ] Monitoring/alerts set up
- [ ] Team trained on admin usage
- [ ] Documentation shared
- [ ] Support plan in place

Good luck! 🎉
