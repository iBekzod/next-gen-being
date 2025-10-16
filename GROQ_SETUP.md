# Quick Setup Guide for Free Groq AI

Get unlimited free AI blog posts in 5 minutes!

## Step 1: Get Your Free Groq API Key

1. Go to https://console.groq.com/
2. Click "Sign Up" (it's completely free!)
3. Verify your email
4. Go to https://console.groq.com/keys
5. Click "Create API Key"
6. Give it a name like "NextGenBeing Blog"
7. Copy the key (starts with `gsk_...`)

## Step 2: Add to Your .env File

Add these lines to your `.env` file:

```env
AI_PROVIDER=groq
GROQ_API_KEY=gsk_your_actual_key_here
GROQ_MODEL=llama-3.1-70b-versatile
```

## Step 3: Test It

Run this command to generate your first AI post:

```bash
# In Docker
docker exec ngb-app php artisan ai:generate-post --draft

# Or locally
php artisan ai:generate-post --draft
```

The `--draft` flag means it won't publish immediately, so you can review it first.

## Step 4: Enable Daily Automation

The system is already configured to generate 1 post daily at 9:00 AM automatically!

Just make sure your cron scheduler is running:

```bash
# Check if cron is set up
docker exec ngb-app crontab -l

# Should show:
# * * * * * cd /var/www/nextgenbeing && php artisan schedule:run >> /dev/null 2>&1
```

## That's It!

You now have:
- ✅ Free AI blog post generation
- ✅ Automatic daily posts
- ✅ High-quality content
- ✅ Zero API costs

## Available Groq Models

All models are **100% FREE** with generous rate limits:

### Recommended: Llama 3.1 70B Versatile
```env
GROQ_MODEL=llama-3.1-70b-versatile
```
- Best quality
- Great for long-form content
- Excellent instruction following

### Fast: Llama 3.1 8B Instant
```env
GROQ_MODEL=llama-3.1-8b-instant
```
- Fastest inference
- Good quality
- Perfect for quick content

### Alternative: Mixtral 8x7B
```env
GROQ_MODEL=mixtral-8x7b-32768
```
- 32k context window
- Good for complex topics
- Balanced speed/quality

## Manual Commands

```bash
# Generate and publish immediately
php artisan ai:generate-post

# Generate as draft (review before publishing)
php artisan ai:generate-post --draft

# Generate for specific category
php artisan ai:generate-post --category=technology

# Generate premium content
php artisan ai:generate-post --premium

# Combine options
php artisan ai:generate-post --category=web-development --draft
```

## Verify It's Working

Check your posts table:

```bash
docker exec ngb-app php artisan tinker
>>> App\Models\Post::latest()->first();
```

Or check the admin panel:
https://nextgenbeing.com/admin/posts

## Troubleshooting

### Error: "API key not configured"
Make sure you added `GROQ_API_KEY` to your `.env` file and ran:
```bash
docker exec ngb-app php artisan config:clear
```

### Error: "Rate limit exceeded"
Groq's free tier is very generous, but if you hit limits, wait a few minutes. The free tier includes:
- 30 requests per minute
- 6,000 requests per day

### Posts not generating daily
Check if cron is running:
```bash
docker exec ngb-app php artisan schedule:list
```

Should show:
```
0 9 * * * php artisan ai:generate-post
```

## Cost Comparison

| Provider | Cost per Post | Monthly (30 posts) |
|----------|---------------|-------------------|
| **Groq** | **$0.00** | **$0.00** ⭐ |
| OpenAI GPT-4 | $0.15 | $4.50 |
| OpenAI GPT-3.5 | $0.02 | $0.60 |
| Anthropic Claude | $0.01 | $0.30 |

**Winner: Groq - Fast, Free, and High Quality!**

## Support

- Groq Docs: https://console.groq.com/docs
- Groq Status: https://status.groq.com/
- Models List: https://console.groq.com/docs/models

Need help? Check the logs:
```bash
docker exec ngb-app tail -f storage/logs/laravel.log | grep "AI post"
```
