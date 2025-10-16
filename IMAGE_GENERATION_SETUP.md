# AI Image Generation for Blog Posts

Automatically generate stunning featured images for your AI-generated blog posts using **Stability AI** or **Unsplash**.

## Quick Setup

### Option 1: Stability AI (Recommended) 💎

**Why**: Custom AI-generated illustrations perfectly matched to your content
**Cost**: ~$0.002 per image (very cheap!)
**Quality**: Excellent, unique images

#### Get API Key

1. Visit https://platform.stability.ai/
2. Sign up for an account
3. Go to API Keys section
4. Generate a new API key
5. Copy the key

#### Configure

Add to your `.env`:
```env
STABILITY_API_KEY=sk-your-key-here
```

### Option 2: Unsplash (Free) 🆓

**Why**: Professional stock photos, completely free
**Cost**: $0.00
**Quality**: High-quality professional photos

#### Get API Key

1. Visit https://unsplash.com/developers
2. Create an account / log in
3. Go to "Your apps" → "New Application"
4. Accept terms and create app
5. Copy your "Access Key"

#### Configure

Add to your `.env`:
```env
UNSPLASH_ACCESS_KEY=your-access-key-here
```

### Option 3: Both (Smart Fallback) 🎯

Configure both for best results:
```env
STABILITY_API_KEY=sk-your-stability-key
UNSPLASH_ACCESS_KEY=your-unsplash-key
```

**How it works**:
1. Tries Stability AI first (custom images)
2. Falls back to Unsplash if Stability fails
3. Skips images if both fail

## How It Works

### Automatic Image Generation

When you run:
```bash
php artisan ai:generate-post
```

The system automatically:
1. **Analyzes** the post title and category
2. **Generates** an AI prompt for relevant imagery
3. **Creates/Fetches** a beautiful featured image
4. **Optimizes** and stores it locally
5. **Attaches** it to the blog post

### Image Prompts

For **Stability AI**, the system creates intelligent prompts based on topic:

**Programming/Code Topics**:
```
"Modern tech aesthetic with code elements, clean design, vibrant colors..."
```

**AI/Machine Learning**:
```
"Futuristic AI theme with neural networks, abstract tech patterns, glowing elements..."
```

**Web Development**:
```
"Modern web design aesthetic, browser interface, responsive layouts..."
```

**Generic Tech**:
```
"Abstract technology concept, modern digital design, professional look..."
```

### For Unsplash

Extracts keywords from the topic and searches for relevant professional photos.

## Features

### Automatic Optimization
- ✅ Proper sizing (1024x1024 for Stability AI)
- ✅ Compressed and optimized
- ✅ Stored in `/storage/app/public/posts/featured/`
- ✅ SEO-friendly filenames
- ✅ Alt text from post title

### SEO Benefits
- ✅ Open Graph images for social sharing
- ✅ Twitter card images
- ✅ Rich snippets in search results
- ✅ Better click-through rates

### Smart Fallbacks
- ⚠️ If Stability API fails → tries Unsplash
- ⚠️ If Unsplash fails → generic tech search
- ⚠️ If all fail → post created without image
- ⚠️ Never blocks post generation

## Testing

### Test Image Generation

```bash
# Generate a test post with image
php artisan ai:generate-post --draft

# Check the output
🤖 Starting AI post generation...
📊 Analyzing trending topics...
✍️  Generating content for: ...
🎨 Generating featured image...
   🎨 Using: Stability AI
   ✅ Image generated successfully!
✅ Post created successfully!
```

### Verify Image

Check the generated image:
```bash
ls -lh storage/app/public/posts/featured/
```

You should see a `.png` (Stability) or `.jpg` (Unsplash) file.

## Cost Analysis

### Stability AI
- **Per Image**: ~$0.002
- **Daily Post**: $0.002/day = $0.06/month
- **Quality**: Custom, unique, perfectly matched

### Unsplash
- **Per Image**: $0.00 (FREE!)
- **Daily Post**: $0.00/month
- **Quality**: Professional stock photos
- **Limitation**: Not custom to your content

### Recommendation

For a blog with **daily AI posts**:
- **With Stability AI**: $0.06/month for images
- **With Unsplash**: $0.00/month
- **Total with Stability**: $0.06/month (negligible cost for custom images!)

**Verdict**: Use Stability AI! The cost is minimal and the custom images are much better for engagement.

## Customization

### Change Image Style

Edit `app/Services/ImageGenerationService.php`, line ~82:

```php
private function createImagePrompt(string $title, string $topic): string
{
    // Customize your base prompt
    $basePrompt = "Your custom style here...";

    // Add your preferred aesthetic
    $basePrompt .= "minimalist, pastel colors, soft gradients...";

    return $basePrompt;
}
```

### Change Image Size

For Stability AI, edit line ~72:

```php
'height' => 1024,  // Change to 512, 768, etc.
'width' => 1024,   // Square, or make rectangular
```

### Change Style Preset

Available presets:
- `digital-art` (default)
- `photographic`
- `anime`
- `3d-model`
- `cinematic`
- `fantasy-art`

Edit line ~78:

```php
'style_preset' => 'photographic',  // or any other
```

## Troubleshooting

### Stability AI Errors

**Error**: "Invalid API key"
```bash
# Verify your key is correct
grep STABILITY_API_KEY .env

# Test the key
curl https://api.stability.ai/v1/user/account \
  -H "Authorization: Bearer YOUR_KEY"
```

**Error**: "Insufficient credits"
- Visit https://platform.stability.ai/
- Add credits to your account
- $10 = ~5,000 images!

### Unsplash Errors

**Error**: "Rate limit exceeded"
- Free tier: 50 requests/hour
- Wait an hour or upgrade account

**Error**: "Invalid access key"
- Verify key in Unsplash dashboard
- Make sure app is active

### No Images Generated

Check logs:
```bash
tail -f storage/logs/laravel.log | grep -i image
```

Common issues:
1. API keys not set in `.env`
2. Storage permissions
3. Network/firewall blocking API calls

### Storage Issues

Ensure storage is linked:
```bash
php artisan storage:link
```

Check permissions:
```bash
chmod -R 775 storage/app/public
chown -R www-data:www-data storage/app/public
```

## Advanced Usage

### Manual Image Generation

In your code:
```php
use App\Services\ImageGenerationService;

$imageService = app(ImageGenerationService::class);

$imageUrl = $imageService->generateFeaturedImage(
    'Master React Hooks in 2025',
    'Web Development'
);
```

### Check Provider Status

```php
$imageService = app(ImageGenerationService::class);

if ($imageService->isAvailable()) {
    echo "Using: " . $imageService->getProvider();
}
```

## API Documentation

### Stability AI
- Docs: https://platform.stability.ai/docs
- Pricing: https://platform.stability.ai/pricing
- Models: https://platform.stability.ai/docs/features/api-parameters

### Unsplash
- Docs: https://unsplash.com/documentation
- Guidelines: https://help.unsplash.com/en/articles/2511245-unsplash-api-guidelines
- Attribution: Required for free tier (automatically handled)

## Examples

### With Stability AI

Generated image for: **"Master React Hooks: 7 Advanced Patterns"**
- Clean, modern design
- Code-themed aesthetic
- React colors and elements
- Professional and eye-catching

### With Unsplash

Fetched image for: **"The Future of AI in Web Development"**
- High-quality tech photo
- Relevant to AI/technology
- Professional photography
- Instant availability

## Performance

### Generation Time
- **Stability AI**: ~10-30 seconds
- **Unsplash**: ~2-5 seconds
- **Total Post**: ~40-60 seconds with Stability

### Storage
- **Per Image**: ~200-500KB (optimized)
- **Monthly** (30 posts): ~6-15MB
- **Yearly**: ~72-180MB (negligible)

## Best Practices

### Do's ✅
- Use Stability AI for custom, branded images
- Use Unsplash as fallback for reliability
- Review generated images periodically
- Update prompts to match your brand aesthetic
- Monitor API costs (though they're minimal)

### Don'ts ❌
- Don't rely solely on Unsplash (images may not match content perfectly)
- Don't skip image generation (hurts SEO and engagement)
- Don't forget to attribute Unsplash (if using their free tier)
- Don't use massive image sizes (wastes bandwidth)

## Monitoring

### Check Generated Images

```bash
# List recent images
ls -lt storage/app/public/posts/featured/ | head -10

# Check image sizes
du -sh storage/app/public/posts/featured/
```

### Track API Usage

**Stability AI**:
- Visit https://platform.stability.ai/usage
- View credits used
- Set budget alerts

**Unsplash**:
- Visit https://unsplash.com/oauth/applications
- Check API usage stats
- Monitor rate limits

## Conclusion

With automated AI image generation, your blog posts will:
- 📸 Look professional and engaging
- 🔍 Rank better in search (images help SEO)
- 📱 Share better on social media (Open Graph images)
- 👁️ Get more clicks (visual content performs better)
- 💰 Cost almost nothing ($0.06/month with Stability AI!)

Set it up once and enjoy beautiful images on every post automatically! 🎨
