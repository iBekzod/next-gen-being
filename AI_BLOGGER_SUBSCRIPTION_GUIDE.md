# AI-Powered Content Generation with Subscription Model

## Overview

This system allows bloggers to generate AI-powered content (text + images) in two ways:
1. **Free Tier**: Use their own API keys (Groq, Unsplash)
2. **Paid Tiers**: Subscribe to use platform's premium AI (GPT-4, DALL-E 3, Claude)

## Business Model

### Revenue Streams

**Current Revenue**:
- User subscriptions (LemonSqueezy)
- Blogger milestone payouts (70/30 split)

**New Revenue Stream**: AI Subscriptions for Bloggers
- Basic: $9.99/month - 20 AI posts, 20 images
- Premium: $29.99/month - 100 AI posts, 100 images
- Enterprise: $99.99/month - Unlimited AI posts & images

### Pricing Calculation

**Platform Costs (Using Premium AI)**:
- GPT-4 Turbo: ~$0.03 per 1000 tokens × 2000 tokens = $0.06 per post
- DALL-E 3: $0.04 per image (standard quality)
- **Total per post with image**: ~$0.10

**Revenue per Tier**:
- Basic ($9.99): 20 posts = $0.50 cost → **$9.49 profit** (95% margin)
- Premium ($29.99): 100 posts = $10 cost → **$19.99 profit** (67% margin)
- Enterprise ($99.99): Heavy users, break-even at ~1000 posts/month

**Realistic Profit**:
- Most bloggers use 5-10 posts/month
- Basic tier: ~$9 profit per subscriber
- Premium tier: ~$25 profit per subscriber

**Target**: 100 paid AI subscribers = $1,000-2,000/month profit

---

## AI Tiers & Limits

### Free Tier
**Cost**: $0
**Limits**:
- ✅ Bring your own API keys (Groq, Unsplash)
- ⚠️ No platform AI access
- ⚠️ Lower quality (Llama vs GPT-4)

### Basic Tier ($9.99/month)
**Includes**:
- 20 AI-generated posts/month
- 20 AI-generated images/month
- GPT-4 Turbo for content
- DALL-E 3 for images
- Priority generation (faster)
- No API key required

### Premium Tier ($29.99/month)
**Includes**:
- 100 AI-generated posts/month
- 100 AI-generated images/month
- GPT-4 Turbo or Claude 3 Opus
- DALL-E 3 or Midjourney
- Priority generation
- Advanced prompts
- SEO optimization

### Enterprise Tier ($99.99/month)
**Includes**:
- Unlimited AI-generated posts
- Unlimited AI-generated images
- All premium AI models
- Custom fine-tuned models (future)
- Dedicated support
- API access (future)

---

## Database Schema

### Users Table (Added Columns)

```sql
-- AI subscription tier
ai_tier ENUM('free', 'basic', 'premium', 'enterprise') DEFAULT 'free'

-- Encrypted API keys (for free tier users)
groq_api_key TEXT NULL
openai_api_key TEXT NULL
unsplash_api_key TEXT NULL

-- Usage tracking
ai_posts_generated INT DEFAULT 0
ai_images_generated INT DEFAULT 0
ai_tier_starts_at TIMESTAMP NULL
ai_tier_expires_at TIMESTAMP NULL

-- Monthly limits
monthly_ai_posts_limit INT NULL  -- NULL = unlimited
monthly_ai_images_limit INT NULL

-- Reset tracking
ai_usage_reset_date DATE NULL  -- Resets monthly
```

---

## Implementation

### 1. Enhanced AI Generation Service

**File**: `app/Services/EnhancedAIGenerationService.php`

**Key Features**:
- Checks user's AI tier
- Uses platform keys for paid tiers
- Uses user's keys for free tier
- Tracks usage and enforces limits
- Auto-resets monthly quotas
- Supports multiple AI providers

**Methods**:
```php
generateContent(User $user, string $prompt): array
generateImage(User $user, string $prompt): string
checkAIQuota(User $user, string $type): bool
resetMonthlyUsage(User $user): void
upgradeAITier(User $user, string $tier): void
```

### 2. Blogger Post Creation Resource

**Enhanced with AI Features**:
- Inline AI generation buttons
- Real-time preview
- One-click "Generate with AI"
- Quota display
- Upgrade prompts when limit reached

### 3. AI Settings Page

**Blogger Dashboard → AI Settings**:
- View current tier
- View usage (15/20 posts used)
- Add own API keys (free tier)
- Upgrade subscription
- View billing

---

## User Flow

### Free Tier Blogger

1. **Initial Setup**:
   ```
   Blogger Dashboard → AI Settings
   → "Add Your API Keys"
   → Enter Groq API key
   → Enter Unsplash API key
   → Save
   ```

2. **Creating Post with AI**:
   ```
   My Posts → Create Post
   → Click "Generate with AI" button
   → Enter prompt: "Write about Laravel best practices"
   → System uses blogger's Groq key
   → Content generated (using their quota)
   → Click "Generate Image"
   → System uses blogger's Unsplash key
   → Image added
   → Publish!
   ```

### Paid Tier Blogger

1. **Subscribe**:
   ```
   AI Settings → "Upgrade to Basic"
   → Redirects to LemonSqueezy checkout
   → Subscribe ($9.99/month)
   → Redirect back, tier activated
   ```

2. **Creating Post with AI**:
   ```
   My Posts → Create Post
   → Shows: "15/20 AI posts remaining this month"
   → Click "Generate with AI" button
   → Enter prompt: "Write about Laravel best practices"
   → System uses platform's GPT-4
   → High-quality content generated instantly
   → Click "Generate Image"
   → System uses platform's DALL-E 3
   → Custom image created
   → Publish!
   ```

3. **Quota Exceeded**:
   ```
   Try to generate 21st post
   → "Monthly limit reached"
   → "Upgrade to Premium (100 posts) or wait until [reset date]"
   → Click upgrade → Seamless tier change
   ```

---

## Integration with LemonSqueezy

### Products to Create

**In LemonSqueezy Dashboard**:

1. **AI Basic**
   - Name: "AI Content Generation - Basic"
   - Price: $9.99/month recurring
   - Variant ID: `ai-basic-monthly`

2. **AI Premium**
   - Name: "AI Content Generation - Premium"
   - Price: $29.99/month recurring
   - Variant ID: `ai-premium-monthly`

3. **AI Enterprise**
   - Name: "AI Content Generation - Enterprise"
   - Price: $99.99/month recurring
   - Variant ID: `ai-enterprise-monthly`

### Webhook Handler

**File**: `app/Http/Controllers/LemonSqueezyWebhookController.php`

**Update to handle AI subscriptions**:
```php
public function handleSubscriptionCreated($payload)
{
    $variantId = $payload['data']['attributes']['variant_id'];
    $customerId = $payload['data']['attributes']['customer_id'];

    // Find user by LemonSqueezy customer ID
    $user = User::where('lemonsqueezy_customer_id', $customerId)->first();

    // Determine AI tier from variant
    $aiTier = match($variantId) {
        config('services.lemonsqueezy.ai_basic_variant') => 'basic',
        config('services.lemonsqueezy.ai_premium_variant') => 'premium',
        config('services.lemonsqueezy.ai_enterprise_variant') => 'enterprise',
        default => 'free',
    };

    // Set limits
    $limits = match($aiTier) {
        'basic' => ['posts' => 20, 'images' => 20],
        'premium' => ['posts' => 100, 'images' => 100],
        'enterprise' => ['posts' => null, 'images' => null], // unlimited
        default => ['posts' => 0, 'images' => 0],
    };

    // Update user
    $user->update([
        'ai_tier' => $aiTier,
        'monthly_ai_posts_limit' => $limits['posts'],
        'monthly_ai_images_limit' => $limits['images'],
        'ai_tier_starts_at' => now(),
        'ai_tier_expires_at' => now()->addMonth(),
        'ai_usage_reset_date' => now()->startOfMonth()->addMonth(),
        'ai_posts_generated' => 0,
        'ai_images_generated' => 0,
    ]);
}
```

---

## API Keys Encryption

**Security**: User's API keys are encrypted at rest.

**File**: `app/Models/User.php`

**Add to casts**:
```php
protected $casts = [
    'groq_api_key' => 'encrypted',
    'openai_api_key' => 'encrypted',
    'unsplash_api_key' => 'encrypted',
    'ai_tier_starts_at' => 'datetime',
    'ai_tier_expires_at' => 'datetime',
    'ai_usage_reset_date' => 'date',
];
```

**Helper methods**:
```php
// Check if user has AI quota
public function hasAIQuota(string $type = 'post'): bool
{
    // Reset if new month
    if ($this->ai_usage_reset_date && $this->ai_usage_reset_date->isPast()) {
        $this->resetMonthlyAI();
    }

    if ($type === 'post') {
        // Unlimited for enterprise
        if ($this->monthly_ai_posts_limit === null) return true;
        return $this->ai_posts_generated < $this->monthly_ai_posts_limit;
    }

    if ($type === 'image') {
        if ($this->monthly_ai_images_limit === null) return true;
        return $this->ai_images_generated < $this->monthly_ai_images_limit;
    }

    return false;
}

public function incrementAIUsage(string $type): void
{
    if ($type === 'post') {
        $this->increment('ai_posts_generated');
    } elseif ($type === 'image') {
        $this->increment('ai_images_generated');
    }
}

public function resetMonthlyAI(): void
{
    $this->update([
        'ai_posts_generated' => 0,
        'ai_images_generated' => 0,
        'ai_usage_reset_date' => now()->startOfMonth()->addMonth(),
    ]);
}

public function getAIProvider(): string
{
    return match($this->ai_tier) {
        'premium', 'enterprise' => 'openai-gpt4', // Platform's GPT-4
        'basic' => 'openai-gpt35', // Platform's GPT-3.5
        'free' => $this->groq_api_key ? 'groq-user' : 'none',
        default => 'none',
    };
}
```

---

## Enhanced Post Creation UI

### Filament Resource Updates

**File**: `app/Filament/Blogger/Resources/MyPostResource.php`

**Add AI generation section**:
```php
Forms\Components\Section::make('AI Content Generation')
    ->schema([
        Forms\Components\Placeholder::make('ai_quota')
            ->label('AI Usage This Month')
            ->content(function () {
                $user = Auth::user();
                $postLimit = $user->monthly_ai_posts_limit ?? '∞';
                $imageLimit = $user->monthly_ai_images_limit ?? '∞';

                return "Posts: {$user->ai_posts_generated}/{$postLimit} • Images: {$user->ai_images_generated}/{$imageLimit}";
            }),

        Forms\Components\TextInput::make('ai_prompt')
            ->label('AI Prompt')
            ->placeholder('e.g., Write a comprehensive guide about Laravel 11 middleware')
            ->helperText('Describe what you want the AI to write about')
            ->suffixAction(
                Forms\Components\Actions\Action::make('generate_content')
                    ->label('Generate with AI')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Generate Content with AI?')
                    ->modalDescription(function () {
                        $user = Auth::user();
                        if (!$user->hasAIQuota('post')) {
                            return 'You have reached your monthly AI limit. Upgrade your plan or wait until next month.';
                        }
                        return 'This will use 1 AI post credit from your monthly quota.';
                    })
                    ->action(function ($set, $get) {
                        $user = Auth::user();
                        $service = app(EnhancedAIGenerationService::class);

                        if (!$user->hasAIQuota('post')) {
                            Notification::make()
                                ->title('AI Quota Exceeded')
                                ->body('Upgrade your plan to generate more content')
                                ->warning()
                                ->send();
                            return;
                        }

                        try {
                            $result = $service->generateContent($user, $get('ai_prompt'));

                            // Fill in the form fields
                            $set('title', $result['title']);
                            $set('excerpt', $result['excerpt']);
                            $set('content', $result['content']);
                            $set('tags', $result['tags']);

                            // Increment usage
                            $user->incrementAIUsage('post');

                            Notification::make()
                                ->title('Content Generated!')
                                ->body('AI has generated your post. Review and edit as needed.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Generation Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
            ),

        Forms\Components\TextInput::make('image_prompt')
            ->label('Image Prompt')
            ->placeholder('e.g., Modern Laravel development workspace with code editor')
            ->suffixAction(
                Forms\Components\Actions\Action::make('generate_image')
                    ->label('Generate Image')
                    ->icon('heroicon-o-photo')
                    ->color('success')
                    ->action(function ($set, $get) {
                        $user = Auth::user();
                        $service = app(EnhancedAIGenerationService::class);

                        if (!$user->hasAIQuota('image')) {
                            Notification::make()
                                ->title('AI Quota Exceeded')
                                ->body('Upgrade your plan to generate more images')
                                ->warning()
                                ->send();
                            return;
                        }

                        try {
                            $imagePath = $service->generateImage($user, $get('image_prompt'));
                            $set('featured_image', $imagePath);
                            $user->incrementAIUsage('image');

                            Notification::make()
                                ->title('Image Generated!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Generation Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
            ),

        Forms\Components\Actions::make([
            Forms\Components\Actions\Action::make('upgrade_ai')
                ->label('Upgrade AI Plan')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('warning')
                ->url(fn () => route('blogger.ai-settings'))
                ->visible(fn () => Auth::user()->ai_tier === 'free'),
        ]),
    ])
    ->collapsible()
    ->collapsed(false),
```

---

## AI Settings Page

**Create new Filament page**: `app/Filament/Blogger/Pages/AISettings.php`

```php
<?php

namespace App\Filament\Blogger\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class AISettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static string $view = 'filament.blogger.pages.ai-settings';
    protected static ?string $navigationLabel = 'AI Settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;

    public function mount(): void
    {
        $user = Auth::user();

        // Check for monthly reset
        if ($user->ai_usage_reset_date && $user->ai_usage_reset_date->isPast()) {
            $user->resetMonthlyAI();
        }

        $this->form->fill([
            'groq_api_key' => $user->groq_api_key,
            'openai_api_key' => $user->openai_api_key,
            'unsplash_api_key' => $user->unsplash_api_key,
        ]);
    }

    protected function getFormSchema(): array
    {
        $user = Auth::user();

        return [
            Forms\Components\Section::make('Current AI Plan')
                ->schema([
                    Forms\Components\Placeholder::make('tier')
                        ->content(ucfirst($user->ai_tier) . ' Tier'),

                    Forms\Components\Placeholder::make('posts_usage')
                        ->label('Posts Generated This Month')
                        ->content(function () use ($user) {
                            $limit = $user->monthly_ai_posts_limit ?? '∞';
                            $percentage = $limit !== '∞' ? ($user->ai_posts_generated / $limit * 100) : 0;
                            return "{$user->ai_posts_generated} / {$limit} ({$percentage}%)";
                        }),

                    Forms\Components\Placeholder::make('images_usage')
                        ->label('Images Generated This Month')
                        ->content(function () use ($user) {
                            $limit = $user->monthly_ai_images_limit ?? '∞';
                            $percentage = $limit !== '∞' ? ($user->ai_images_generated / $limit * 100) : 0;
                            return "{$user->ai_images_generated} / {$limit} ({$percentage}%)";
                        }),

                    Forms\Components\Placeholder::make('reset_date')
                        ->label('Usage Resets')
                        ->content($user->ai_usage_reset_date?->format('M d, Y') ?? 'N/A'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Your API Keys (Free Tier)')
                ->description('Add your own API keys to use AI features for free')
                ->schema([
                    Forms\Components\TextInput::make('groq_api_key')
                        ->label('Groq API Key')
                        ->password()
                        ->helperText('Get from: https://console.groq.com/keys'),

                    Forms\Components\TextInput::make('openai_api_key')
                        ->label('OpenAI API Key (Optional)')
                        ->password()
                        ->helperText('Get from: https://platform.openai.com/api-keys'),

                    Forms\Components\TextInput::make('unsplash_api_key')
                        ->label('Unsplash API Key')
                        ->password()
                        ->helperText('Get from: https://unsplash.com/developers'),
                ])
                ->visible($user->ai_tier === 'free'),
        ];
    }
}
```

---

## Subscription Upgrade Flow

### Pricing Page

**Route**: `/blogger/ai-pricing`

**View**: Simple comparison table:
```
+----------------+------------------+------------------+------------------+
|                |  Free            | Basic ($9.99)    | Premium ($29.99) |
+----------------+------------------+------------------+------------------+
| AI Posts/month | 0 (BYO key)      | 20               | 100              |
| AI Images      | 0 (BYO key)      | 20               | 100              |
| AI Model       | Llama (your key) | GPT-4 Turbo      | GPT-4 + Claude   |
| Image Model    | Unsplash         | DALL-E 3         | DALL-E 3         |
| Priority       | No               | Yes              | Yes              |
| Support        | Community        | Email            | Priority         |
+----------------+------------------+------------------+------------------+
```

### Checkout Flow

1. **Click "Upgrade to Basic"**
2. **Redirect to LemonSqueezy** with checkout URL
3. **Complete payment**
4. **Webhook activates tier**
5. **Redirect back to dashboard** with success message

---

## Revenue Projection

### Conservative Estimate

**Assumptions**:
- 1000 total bloggers
- 10% conversion to paid AI tiers
- Average tier: Basic ($9.99)

**Monthly Revenue**:
- 100 paid subscribers × $9.99 = $999
- Platform AI cost: ~$50 (avg 5 posts per user)
- **Net Profit**: $950/month = $11,400/year

### Optimistic Estimate

**Assumptions**:
- 1000 total bloggers
- 20% conversion
- Mix: 150 Basic + 30 Premium + 20 Enterprise

**Monthly Revenue**:
- 150 × $9.99 = $1,498.50
- 30 × $29.99 = $899.70
- 20 × $99.99 = $1,999.80
- **Total**: $4,398/month
- Platform cost: ~$200
- **Net Profit**: $4,198/month = $50,376/year

---

## Marketing Strategy

### Email Campaign

**Subject**: "Introducing AI-Powered Content Generation"

```
Hi [Blogger],

Writing blog posts just got 10x easier!

We've launched AI-powered content generation:
✨ Generate full blog posts in seconds
🎨 Create custom images with AI
📈 SEO-optimized content

FREE TRIAL: Try your first 5 AI posts free!
Then continue with Basic ($9.99/mo) or use your own API keys.

[Try AI Generation Now]

Happy blogging!
```

### In-App Prompts

**When creating post**:
- "⚡ Generate this post with AI in 10 seconds"
- "🎨 Need an image? Generate with DALL-E 3"
- Banner: "Try AI Generation - First 5 posts free!"

### Dashboard Widget

```
┌─────────────────────────────────────┐
│ 🚀 Boost Your Content with AI       │
│                                     │
│ Generate blog posts 10x faster     │
│ • GPT-4 powered writing            │
│ • Custom DALL-E images             │
│ • SEO optimized content            │
│                                     │
│ [Try Free] [See Plans]             │
└─────────────────────────────────────┘
```

---

## Implementation Checklist

### Phase 1: Foundation (Week 1)
- [ ] Run migration for AI settings
- [ ] Update User model with casts and helpers
- [ ] Create EnhancedAIGenerationService
- [ ] Add API key encryption

### Phase 2: LemonSqueezy Integration (Week 2)
- [ ] Create 3 AI subscription products in LemonSqueezy
- [ ] Update webhook handler for AI subscriptions
- [ ] Test subscription flow end-to-end

### Phase 3: UI/UX (Week 3)
- [ ] Add AI generation section to MyPostResource
- [ ] Create AI Settings page
- [ ] Create AI pricing page
- [ ] Add usage quota displays

### Phase 4: Testing & Launch (Week 4)
- [ ] Test free tier (user's own keys)
- [ ] Test paid tiers (platform keys)
- [ ] Test quota enforcement
- [ ] Test monthly reset
- [ ] Soft launch to 10 beta bloggers
- [ ] Gather feedback
- [ ] Full launch with email campaign

---

## Conclusion

This AI subscription model creates a **win-win**:

**For Bloggers**:
- ✅ Easy content creation
- ✅ Professional quality with GPT-4
- ✅ Time savings
- ✅ Option to BYO keys (free)

**For Platform**:
- 💰 New revenue stream ($1k-4k/month potential)
- 📈 95% profit margins on Basic tier
- 🎯 Sticky feature (high retention)
- 🚀 Scales automatically

**Next Steps**: Implement Phase 1 and test with free tier first, then add paid subscriptions once proven.
