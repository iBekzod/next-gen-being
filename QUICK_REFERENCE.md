# Quick Reference Guide - AI Learning Platform

## 🚀 Start Here (First Time Setup)

```bash
# 1. Verify everything is ready
php artisan ai-learning:verify-setup

# 2. Run database migrations
php artisan migrate --step

# 3. Seed categories and tags
php artisan db:seed --class=AILearningCategoriesAndTagsSeeder

# 4. Create sample products (for testing)
php artisan ai-learning:create-samples --count=10

# 5. Start the application
php artisan serve --port=9070

# 6. In another terminal, start scheduler
php artisan schedule:work
```

Then visit:
- Marketplace: http://localhost:9070/resources
- Admin: http://localhost:9070/admin

---

## 📋 Common Commands

### Content Generation
```bash
# Test tutorial generation (dry run)
php artisan ai-learning:generate-weekly --day=Monday --dry-run

# Generate tutorial for today
php artisan ai-learning:generate-weekly

# Generate specific day
php artisan ai-learning:generate-weekly --day=Wednesday

# Generate prompts
php artisan ai-learning:generate-prompts --count=5

# Generate samples (for testing)
php artisan ai-learning:create-samples --count=10
```

### Database
```bash
# Run migrations
php artisan migrate

# Run migrations step by step
php artisan migrate --step

# Rollback last batch
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset

# Seed initial data
php artisan db:seed --class=AILearningCategoriesAndTagsSeeder

# Use tinker to query database
php artisan tinker
>>> DigitalProduct::count()
>>> DigitalProduct::published()->count()
>>> ProductPurchase::where('status', 'completed')->count()
```

### Scheduler
```bash
# View all scheduled tasks
php artisan schedule:list

# Test scheduler (watch it run)
php artisan schedule:work

# Test a specific command
php artisan schedule:test ai-learning:generate-weekly
```

### Admin & Views
```bash
# Start development server
php artisan serve --port=9070

# Serve on different port
php artisan serve --port=8000

# Access admin panel
# http://localhost:9070/admin

# Access marketplace
# http://localhost:9070/resources
```

### Cache & Configuration
```bash
# Clear all caches
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear

# Clear route cache
php artisan route:clear

# Rebuild all caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📊 Monitoring

### Check System Health
```bash
# Verify setup
php artisan ai-learning:verify-setup

# View logs
tail -f storage/logs/laravel.log

# Check database
php artisan tinker
>>> Post::whereDate('published_at', today())->count()
>>> DigitalProduct::published()->count()
>>> ProductPurchase::where('status', 'completed')->count()
```

### Monitor Scheduler
```bash
# Check if running
ps aux | grep schedule:work

# View schedule list
php artisan schedule:list

# Check supervisor status (production)
sudo supervisorctl status laravel-scheduler
```

---

## 🌐 URLs (After Running Application)

### Public URLs
```
/resources                           → Product listing
/resources/{product:slug}            → Product details
/resources/downloads                 → Download center
/resources/my-purchases              → Purchase history
```

### Admin URLs
```
/admin                               → Dashboard
/admin/digital-products              → Product management
/admin/digital-products/create       → Create product
/admin/digital-products/{id}/edit    → Edit product
```

---

## 📁 Important Directories

```
app/
├─ Models/DigitalProduct.php         → Product model
├─ Models/ProductPurchase.php        → Purchase model
├─ Console/Commands/                 → Automation commands
│  ├─ GenerateWeeklyTutorialCommand.php
│  ├─ GeneratePromptLibraryCommand.php
│  └─ CreateSampleDigitalProductsCommand.php
├─ Http/Controllers/DigitalProductController.php
├─ Services/DigitalProductService.php
└─ Filament/Resources/DigitalProductResource.php

config/
└─ ai-learning.php                   → Topic and schedule config

resources/views/digital-products/
├─ index.blade.php                   → Product listing
├─ show.blade.php                    → Product details
├─ my-purchases.blade.php            → Purchase history
└─ download-index.blade.php          → Download center

storage/app/private/                 → Uploaded product files
```

---

## 🔧 Configuration Files

### `.env` - Critical Variables
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=ngb-database
DB_DATABASE=nextgenbeing

# AI Providers (at least one required)
OPENAI_API_KEY=sk-...
GROQ_API_KEY=gsk-...

# LemonSqueezy (optional for payments)
LEMONSQUEEZY_API_KEY=...
LEMONSQUEEZY_SIGNING_SECRET=...

# Automation flags
AI_LEARNING_ENABLED=true
TUTORIAL_GENERATION_ENABLED=true
PROMPT_LIBRARY_ENABLED=true
```

### `config/ai-learning.php` - Customization
```php
// Edit topics
'tutorial_topics' => [
    'beginner' => [...],
    'intermediate' => [...],
    'advanced' => [...]
]

// Edit schedule
'weekly_schedule' => [
    'monday' => ['type' => 'beginner'],
    'wednesday' => ['type' => 'intermediate'],
    'friday' => ['type' => 'advanced']
]
```

---

## 🧪 Testing Checklist

- [ ] Database connection: `php artisan migrate --dry-run`
- [ ] Models: `php artisan tinker` → `DigitalProduct::count()`
- [ ] Routes: `php artisan route:list | grep digital-products`
- [ ] Commands: `php artisan list | grep ai-learning`
- [ ] Marketplace: Visit `/resources`
- [ ] Admin panel: Visit `/admin`
- [ ] Product creation: Create product in admin
- [ ] File upload: Upload test file
- [ ] Sample products: `php artisan ai-learning:create-samples --count=10`
- [ ] Tutorial generation: `php artisan ai-learning:generate-weekly --day=Monday --dry-run`
- [ ] Scheduler: `php artisan schedule:list` and `php artisan schedule:work`

---

## 🐛 Quick Troubleshooting

### Database Connection Failed
```bash
# Check connection
php artisan tinker
>>> DB::connection()->getPdo()

# Check .env settings
cat .env | grep DB_

# If using Docker, ensure it's running
docker-compose up -d
```

### Products Not Showing
```bash
# Create test products
php artisan ai-learning:create-samples --count=10

# Verify in database
php artisan tinker
>>> DigitalProduct::count()
```

### Scheduler Not Running
```bash
# Start scheduler
php artisan schedule:work

# Or check if already running
ps aux | grep schedule:work

# View scheduled tasks
php artisan schedule:list
```

### AI Generation Fails
```bash
# Check API keys
echo $OPENAI_API_KEY
echo $GROQ_API_KEY

# Test command
php artisan ai-learning:generate-weekly --day=Monday --dry-run

# Check logs
tail -f storage/logs/laravel.log
```

### File Downloads Don't Work
```bash
# Fix permissions
chmod -R 775 storage/app/private

# Verify disk config
php artisan tinker
>>> Storage::disk('private')->exists('test.txt')
```

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| [README_AI_LEARNING.md](README_AI_LEARNING.md) | Platform overview |
| [AI_LEARNING_DEPLOYMENT.md](AI_LEARNING_DEPLOYMENT.md) | Production deployment |
| [IMPLEMENTATION_MANIFEST.md](IMPLEMENTATION_MANIFEST.md) | Complete file listing |
| [IMPLEMENTATION_COMPLETE.md](IMPLEMENTATION_COMPLETE.md) | Status and summary |
| [QUICK_REFERENCE.md](QUICK_REFERENCE.md) | This file |
| [QUICK_START.sh](QUICK_START.sh) | Automated verification |

---

## 💰 Revenue Tracking

```bash
# Check purchases
php artisan tinker
>>> ProductPurchase::where('status', 'completed')->sum('creator_revenue')

# Check creator earnings
>>> BloggerEarning::where('type', 'digital_product_sale')->sum('amount')

# By product
>>> DigitalProduct::find(1)->purchases()->where('status', 'completed')->sum('creator_revenue')
```

---

## 📅 Automation Schedule

| Day | Time | Task | Frequency |
|-----|------|------|-----------|
| Monday | 08:00 | Beginner tutorial | Weekly |
| Wednesday | 08:00 | Intermediate tutorial | Weekly |
| Friday | 08:00 | Advanced tutorial | Monthly |
| 1st | 10:00 | Prompts (10) | Monthly |
| Daily | 22:00 | SEO optimization | Daily |

---

## 🎯 Success Indicators

✅ Everything working when:
- [ ] Marketplace loads at `/resources`
- [ ] Admin accessible at `/admin`
- [ ] Can create product in admin
- [ ] Can view sample products
- [ ] Scheduler running: `ps aux | grep schedule:work`
- [ ] No errors in logs: `tail -f storage/logs/laravel.log`
- [ ] Database connected: `php artisan migrate:status`

---

## 🚀 First Launch Sequence

```bash
# 1. Check everything
php artisan ai-learning:verify-setup

# 2. Setup database
php artisan migrate --step
php artisan db:seed --class=AILearningCategoriesAndTagsSeeder

# 3. Create test data
php artisan ai-learning:create-samples --count=10

# 4. Start services
# Terminal 1:
php artisan serve --port=9070

# Terminal 2:
php artisan schedule:work

# 5. Visit and test
# http://localhost:9070/resources           (marketplace)
# http://localhost:9070/admin               (admin)
# http://localhost:9070/admin/digital-products  (product mgmt)
```

---

## 💡 Pro Tips

1. **Custom Topics**: Edit `config/ai-learning.php` to change topics
2. **Faster Testing**: Use `--dry-run` flag on commands before running real
3. **Monitor Content**: Watch `storage/logs/laravel.log` while scheduler runs
4. **Database Inspect**: Use `php artisan tinker` to quickly check data
5. **Cache Issues**: Always run `php artisan config:clear` after .env changes
6. **Production**: Use supervisor instead of `schedule:work` for better reliability
7. **Backups**: Regular database backups critical (automate with cron)
8. **S3 Storage**: Switch to S3 in production for better scalability

---

**Need more help?** Check the full documentation files or run `php artisan ai-learning:verify-setup` for a system health check.
