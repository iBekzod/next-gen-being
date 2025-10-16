# AI Post Generation

This application includes automated AI-powered blog post generation using **Groq API (100% FREE!)** with Llama 3.3 70B model.

## Features

- **Completely FREE** - No API costs with Groq
- **Lightning Fast** - Groq provides the fastest inference speed
- **High Quality** - Uses Meta's Llama 3.3 70B model (upgraded!)
- Generates complete, SEO-optimized blog posts automatically
- Analyzes trending topics to avoid duplicates
- Creates proper markdown-formatted content (1000-1500 words)
- Automatically assigns categories, tags, and SEO metadata
- Scheduled to run daily at 9:00 AM

## Configuration

### 1. Get Your Free Groq API Key

1. Visit https://console.groq.com/
2. Sign up for a free account
3. Go to API Keys section
4. Create a new API key
5. Copy the key

### 2. Environment Variables

Add these to your `.env` file:

```env
# Groq Configuration (FREE!)
AI_PROVIDER=groq
GROQ_API_KEY=gsk_your_groq_api_key_here
GROQ_MODEL=llama-3.3-70b-versatile

# Optional: OpenAI (if you want to switch)
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4
```

### 2. Schedule Setup

The daily post generation is configured in `routes/console.php` and runs at 9:00 AM daily.

Make sure your cron job is running:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or in Docker (already configured):

```bash
docker exec ngb-app php artisan schedule:work
```

## Manual Usage

### Generate a post immediately

```bash
php artisan ai:generate-post
```

### Options

Generate a draft post (not published):
```bash
php artisan ai:generate-post --draft
```

Generate for a specific category:
```bash
php artisan ai:generate-post --category=web-development
```

Generate with a specific author:
```bash
php artisan ai:generate-post --author=1
```

Generate as premium content:
```bash
php artisan ai:generate-post --premium
```

Use a different AI provider:
```bash
php artisan ai:generate-post --provider=openai
```

Combine options:
```bash
php artisan ai:generate-post --category=technology --author=1 --draft
```

## How It Works

1. **Topic Selection**: The AI analyzes recent posts to avoid duplication and generates a trending topic
2. **Content Generation**: GPT-4 creates a complete article with:
   - Engaging title
   - 1000-1500 words of content
   - Proper markdown formatting with headings
   - Practical examples and insights
   - Conclusion with takeaways
3. **SEO Optimization**: Automatically generates:
   - Meta title and description
   - Keywords
   - Open Graph tags
4. **Categorization**: Assigns to an existing category or creates one
5. **Tagging**: Creates and attaches relevant tags
6. **Publishing**: Published immediately (or saved as draft with --draft flag)

## Customization

### Change Schedule Time

Edit `routes/console.php`:

```php
Schedule::command('ai:generate-post')
    ->dailyAt('14:00')  // Change to 2:00 PM
    ->timezone('America/New_York');  // Set timezone
```

### Change AI Provider

In `.env`:

```env
# Use Groq (Free, Fast)
AI_PROVIDER=groq
GROQ_MODEL=llama-3.3-70b-versatile  # Best quality (NEW!)
GROQ_MODEL=llama-3.1-8b-instant     # Faster, good quality
GROQ_MODEL=openai/gpt-oss-120b      # OpenAI OSS (large)

# Or use OpenAI
AI_PROVIDER=openai
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MODEL=gpt-4
```

### Adjust Content Length

Edit `app/Console/Commands/GenerateAiPost.php`, line ~140:

```php
$prompt = "Write a comprehensive, well-structured blog post about: {$topic['title']}

Requirements:
- 2000-3000 words  // Change this
// ... rest of prompt
```

## Monitoring

### Check Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log | grep "AI post"

# In Docker
docker exec ngb-app tail -f storage/logs/laravel.log | grep "AI post"
```

### Successful Generation Log

```
AI post generated successfully
  post_id: 123
  title: "The Future of AI in Web Development"
  topic: "AI Web Development Trends"
```

### Check Scheduled Tasks

```bash
php artisan schedule:list
```

## Cost Comparison

### Groq (Default - FREE!) ⭐
- **Cost per post**: $0.00
- **Monthly cost**: $0.00
- **Speed**: Fastest (tokens/sec)
- **Model**: Llama 3.3 70B (upgraded!)
- **Rate Limit**: Very generous free tier

### OpenAI (Optional)
With GPT-4:
- ~$0.10-0.20 per post
- ~$3-6 per month for daily posts

With GPT-3.5-turbo:
- ~$0.01-0.02 per post
- ~$0.30-0.60 per month for daily posts

**Recommendation: Stick with Groq - it's free and produces excellent content!**

## Troubleshooting

### API Key Not Working

```bash
# Test Groq API key
php artisan tinker
>>> \Illuminate\Support\Facades\Http::withHeaders(['Authorization' => 'Bearer ' . config('services.groq.api_key')])->get('https://api.groq.com/openai/v1/models');
```

Get your free Groq API key at: https://console.groq.com/keys

### Command Not Found

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# List all commands
php artisan list
```

### Schedule Not Running

```bash
# Test schedule manually
php artisan schedule:run

# Or use schedule:work for continuous running (better for development)
php artisan schedule:work
```

### Posts Not Publishing

Check:
1. Author exists in database
2. Category exists or can be created
3. Database permissions
4. Check logs for errors

## Advanced: Multiple Posts Per Day

Edit `routes/console.php`:

```php
// Generate 3 posts per day at different times
Schedule::command('ai:generate-post')->dailyAt('09:00');
Schedule::command('ai:generate-post')->dailyAt('14:00');
Schedule::command('ai:generate-post')->dailyAt('19:00');
```

Or use cron syntax:

```php
// Every 8 hours
Schedule::command('ai:generate-post')->cron('0 */8 * * *');
```

## Safety Features

- **Duplicate Detection**: Checks recent posts to avoid similar topics
- **Fallback Topics**: Has predefined topics if AI fails
- **Error Handling**: Logs errors without breaking the application
- **Draft Mode**: Test content before publishing
- **Manual Override**: All options can be specified manually

## API Rate Limits

OpenAI has rate limits:
- Free tier: Lower limits
- Paid tier: Higher limits based on usage tier

If you hit rate limits, the command will fail gracefully and log the error.

## Content Quality

The AI generates high-quality content, but we recommend:
- Reviewing posts before publishing (use `--draft`)
- Adding custom images
- Fact-checking technical details
- Adding personal insights or examples
- Customizing based on your audience

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Verify OpenAI API key is valid
3. Test manually: `php artisan ai:generate-post --draft`
4. Check OpenAI API status: https://status.openai.com/
