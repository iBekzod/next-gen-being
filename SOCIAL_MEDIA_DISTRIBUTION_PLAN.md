# Social Media Distribution System - Architecture Plan

## Overview
Automated cross-platform content distribution system that publishes blog content to multiple social media platforms using AI moderation and scheduling.

---

## Content Type Matrix

| Platform | Text Posts | Images | Videos | Links | Optimal Format |
|----------|-----------|--------|--------|-------|---------------|
| **YouTube** | ❌ | Thumbnail | ✅ (Primary) | Description | 1920x1080, MP4, 5-30min |
| **Instagram** | ✅ (Caption) | ✅ | ✅ (Reels) | ❌ (Bio only) | 1080x1080 or 1080x1920 |
| **TikTok** | ✅ (Caption) | ❌ | ✅ (Primary) | ❌ | 1080x1920, MP4, 15-180s |
| **Twitter/X** | ✅ (280 char) | ✅ | ✅ | ✅ | 1200x675, MP4 <2min |
| **Facebook** | ✅ | ✅ | ✅ | ✅ | 1200x630, MP4 |
| **LinkedIn** | ✅ | ✅ | ✅ (Native) | ✅ | 1200x627, MP4 <10min |
| **Telegram** | ✅ | ✅ | ✅ | ✅ | Any format |
| **Pinterest** | ❌ | ✅ (Primary) | ✅ (Pins) | ✅ | 1000x1500 (2:3) |
| **Medium** | ✅ (Primary) | ✅ | ❌ | ✅ | Markdown |
| **Dev.to** | ✅ (Primary) | ✅ | ❌ | ✅ | Markdown |

---

## System Architecture

### Phase 1: Content Preparation
```
Blog Post (Draft)
    ↓
[AI Moderator Review]
    ↓
Approved? → Generate Social Media Formats
    ├── Text Format (Twitter, LinkedIn, Facebook)
    ├── Visual Format (Instagram, Pinterest)
    ├── Video Format (YouTube, TikTok, Reels)
    └── Link Format (Medium, Dev.to)
```

### Phase 2: Format Generation

#### A. **Text Optimization**
```php
Original Blog Post (2000 words)
    ↓
AI generates platform-specific versions:
    - Twitter Thread (10 tweets × 280 chars)
    - LinkedIn Post (1300 chars + hashtags)
    - Facebook Post (500 chars engaging)
    - Instagram Caption (2200 chars + emojis)
    - Telegram Message (formatted with markdown)
```

#### B. **Visual Assets**
```php
Blog Featured Image
    ↓
AI generates variants:
    - YouTube Thumbnail (1280x720)
    - Instagram Square (1080x1080)
    - Instagram Story (1080x1920)
    - Pinterest Pin (1000x1500)
    - Twitter Card (1200x675)
    - LinkedIn Post (1200x627)
```

#### C. **Video Generation** ⭐ NEW
```php
Blog Post Content
    ↓
AI Video Pipeline:
    1. Extract key points (GPT-4)
    2. Generate script with timestamps
    3. Create voiceover (OpenAI TTS/ElevenLabs)
    4. Fetch stock footage (Pexels API)
    5. Generate captions (WebVTT format)
    6. Combine with FFmpeg:
        - Video clips
        - Voiceover
        - Background music
        - Captions overlay
        - Intro/outro branding
    ↓
Output formats:
    - YouTube (1920x1080, 5-15min)
    - TikTok (1080x1920, 60s)
    - Instagram Reel (1080x1920, 90s)
    - LinkedIn Video (1280x720, 3min)
```

### Phase 3: Distribution

#### Option A: Platform Auto-Publishing (NextGen Being Official Accounts)
```php
AI Moderator (Background Command)
    ↓
Runs daily at 9 AM, 12 PM, 6 PM
    ↓
Selects top 3 approved posts
    ↓
Publishes to NextGen Being accounts:
    - YouTube: Full video
    - Instagram: Reel + Carousel post
    - TikTok: Short video
    - Twitter: Thread with video
    - LinkedIn: Article + video
    - Facebook: Post + video
    - Telegram: Channel post
    - Pinterest: Pin with link
```

#### Option B: Blogger Self-Publishing (Checklist System)
```php
Blogger Dashboard → Post Publishing Checklist
    ↓
[ ] Connect your social media APIs
[ ] Review auto-generated content
[ ] Customize captions/titles
[ ] Schedule or publish now
    ↓
One-click publish to:
    ✓ YouTube (requires OAuth)
    ✓ Instagram (via Meta Graph API)
    ✓ Twitter (via Twitter API v2)
    ✓ LinkedIn (via LinkedIn API)
    ✓ TikTok (via TikTok for Business API)
    ✓ Facebook Pages (via Meta Graph API)
    ✓ Telegram (via Bot API)
```

---

## Technical Implementation

### 1. Database Schema Updates

#### New Table: `social_media_accounts`
```sql
CREATE TABLE social_media_accounts (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    platform VARCHAR(50), -- youtube, instagram, twitter, etc.
    account_type VARCHAR(20), -- 'personal', 'platform_official'

    -- OAuth tokens (encrypted)
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at TIMESTAMP,

    -- Platform-specific IDs
    platform_user_id VARCHAR(255),
    platform_username VARCHAR(255),

    -- Account details
    account_name VARCHAR(255),
    account_avatar TEXT,
    follower_count INTEGER,

    -- Settings
    auto_publish BOOLEAN DEFAULT FALSE,
    publish_schedule JSONB, -- {"days": ["mon", "wed"], "times": ["09:00", "18:00"]}

    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    last_published_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

#### New Table: `social_media_posts`
```sql
CREATE TABLE social_media_posts (
    id BIGSERIAL PRIMARY KEY,
    post_id BIGINT REFERENCES posts(id),
    social_media_account_id BIGINT REFERENCES social_media_accounts(id),

    platform VARCHAR(50),
    platform_post_id VARCHAR(255), -- ID from social media platform
    platform_post_url TEXT,

    -- Content variants
    content_text TEXT,
    content_media_url TEXT, -- image or video URL
    content_type VARCHAR(20), -- 'text', 'image', 'video', 'carousel'

    -- Metadata
    caption TEXT,
    hashtags TEXT[],
    mentions TEXT[],

    -- Engagement metrics (synced periodically)
    likes_count INTEGER DEFAULT 0,
    comments_count INTEGER DEFAULT 0,
    shares_count INTEGER DEFAULT 0,
    views_count INTEGER DEFAULT 0,

    -- Publishing
    status VARCHAR(20), -- 'draft', 'scheduled', 'published', 'failed'
    scheduled_at TIMESTAMP,
    published_at TIMESTAMP,
    error_message TEXT,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

#### New Table: `video_generations`
```sql
CREATE TABLE video_generations (
    id BIGSERIAL PRIMARY KEY,
    post_id BIGINT REFERENCES posts(id),
    user_id BIGINT REFERENCES users(id),

    -- Video details
    video_type VARCHAR(20), -- 'youtube', 'tiktok', 'reel', 'short'
    duration_seconds INTEGER,
    resolution VARCHAR(20), -- '1920x1080', '1080x1920'

    -- Generation process
    script TEXT, -- AI-generated script
    voiceover_url TEXT, -- Generated audio file
    video_clips JSONB, -- Stock footage used
    captions_url TEXT, -- WebVTT subtitle file

    -- Final output
    video_url TEXT,
    thumbnail_url TEXT,
    file_size_mb DECIMAL(10, 2),

    -- AI credits used
    ai_credits_used INTEGER,
    generation_cost DECIMAL(10, 2),

    -- Status
    status VARCHAR(20), -- 'queued', 'processing', 'completed', 'failed'
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    error_message TEXT,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### 2. Required API Integrations

#### A. Social Media APIs

**YouTube Data API v3**
- Upload videos
- Set title, description, tags
- Schedule publishing
- Get analytics
```php
'youtube' => [
    'client_id' => env('YOUTUBE_CLIENT_ID'),
    'client_secret' => env('YOUTUBE_CLIENT_SECRET'),
    'redirect_uri' => env('YOUTUBE_REDIRECT_URI'),
],
```

**Instagram Graph API** (via Meta)
- Post photos/videos
- Post carousels
- Publish Reels
- Get insights
```php
'instagram' => [
    'app_id' => env('META_APP_ID'),
    'app_secret' => env('META_APP_SECRET'),
],
```

**Twitter API v2**
- Post tweets
- Create threads
- Upload media
- Get analytics
```php
'twitter' => [
    'api_key' => env('TWITTER_API_KEY'),
    'api_secret' => env('TWITTER_API_SECRET'),
    'bearer_token' => env('TWITTER_BEARER_TOKEN'),
],
```

**LinkedIn API**
- Post articles
- Share videos
- Company pages
- Get analytics
```php
'linkedin' => [
    'client_id' => env('LINKEDIN_CLIENT_ID'),
    'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
],
```

**TikTok for Business API**
- Upload videos
- Post with captions
- Schedule content
```php
'tiktok' => [
    'client_key' => env('TIKTOK_CLIENT_KEY'),
    'client_secret' => env('TIKTOK_CLIENT_SECRET'),
],
```

**Telegram Bot API**
- Post to channels
- Send media
- Format messages
```php
'telegram' => [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'channel_id' => env('TELEGRAM_CHANNEL_ID'),
],
```

#### B. Video Generation APIs

**OpenAI Text-to-Speech**
```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'tts_voice' => env('OPENAI_TTS_VOICE', 'alloy'), // alloy, echo, fable, onyx, nova, shimmer
],
```

**ElevenLabs** (Better quality)
```php
'elevenlabs' => [
    'api_key' => env('ELEVENLABS_API_KEY'),
    'voice_id' => env('ELEVENLABS_VOICE_ID'),
],
```

**Pexels API** (Free stock videos)
```php
'pexels' => [
    'api_key' => env('PEXELS_API_KEY'),
],
```

**Pictory.ai** (Text to video - optional)
```php
'pictory' => [
    'api_key' => env('PICTORY_API_KEY'),
],
```

### 3. Video Generation Pipeline

#### Service: `VideoGenerationService.php`

```php
class VideoGenerationService
{
    public function generateFromPost(Post $post, string $type = 'youtube'): VideoGeneration
    {
        // 1. Extract key points and create script
        $script = $this->generateScript($post, $type);

        // 2. Generate voiceover
        $voiceoverPath = $this->generateVoiceover($script);

        // 3. Fetch relevant stock footage
        $videoClips = $this->fetchStockFootage($post);

        // 4. Generate captions
        $captionsPath = $this->generateCaptions($script);

        // 5. Combine everything with FFmpeg
        $finalVideo = $this->combineVideo([
            'clips' => $videoClips,
            'voiceover' => $voiceoverPath,
            'captions' => $captionsPath,
            'duration' => $this->getTargetDuration($type),
            'resolution' => $this->getResolution($type),
        ]);

        // 6. Generate thumbnail
        $thumbnail = $this->generateThumbnail($post);

        // 7. Save to database
        return VideoGeneration::create([...]);
    }

    protected function generateScript(Post $post, string $type): array
    {
        $targetDuration = match($type) {
            'youtube' => 600, // 10 minutes
            'tiktok' => 60,   // 1 minute
            'reel' => 90,     // 1.5 minutes
            'short' => 60,    // 1 minute
        };

        $prompt = "Convert this blog post into a {$targetDuration}-second video script...";
        // Use GPT-4 to generate timed script with scenes
    }
}
```

---

## Publishing Workflows

### Workflow 1: NextGen Being Official Auto-Publishing

```php
// Console Command: app/Console/Commands/PublishToSocialMedia.php

class PublishToSocialMedia extends Command
{
    protected $signature = 'social:publish {--platform=all}';

    public function handle()
    {
        // 1. Get approved posts that haven't been published
        $posts = Post::where('status', 'published')
            ->where('is_moderated', true)
            ->where('moderation_status', 'approved')
            ->whereDoesntHave('socialMediaPosts', function($q) {
                $q->where('platform', 'youtube')
                  ->where('status', 'published');
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        // 2. For each post, publish to all platforms
        foreach ($posts as $post) {
            $this->publishToYouTube($post);
            $this->publishToInstagram($post);
            $this->publishToTikTok($post);
            $this->publishToTwitter($post);
            $this->publishToLinkedIn($post);
            $this->publishToTelegram($post);
        }
    }
}

// Schedule in app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Publish 3 times daily
    $schedule->command('social:publish')->dailyAt('09:00');
    $schedule->command('social:publish')->dailyAt('14:00');
    $schedule->command('social:publish')->dailyAt('19:00');
}
```

### Workflow 2: Blogger Self-Publishing Checklist

```php
// Filament Resource: SocialMediaPublishingResource.php

class SocialMediaPublishingResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Connected Accounts')
                ->description('Connect your social media accounts to publish')
                ->schema([
                    Repeater::make('accounts')
                        ->relationship('socialMediaAccounts')
                        ->schema([
                            Select::make('platform')
                                ->options([
                                    'youtube' => 'YouTube',
                                    'instagram' => 'Instagram',
                                    'tiktok' => 'TikTok',
                                    'twitter' => 'Twitter/X',
                                    'linkedin' => 'LinkedIn',
                                    'facebook' => 'Facebook',
                                ])
                                ->required(),

                            Actions\Action::make('connect')
                                ->label('Connect Account')
                                ->url(fn($record) => route('oauth.redirect', $record->platform)),
                        ]),
                ]),

            Section::make('Publishing Checklist')
                ->schema([
                    CheckboxList::make('platforms_to_publish')
                        ->options(fn($record) => $record->getConnectedPlatforms())
                        ->descriptions([
                            'youtube' => 'Video will be uploaded (5-15min)',
                            'instagram' => 'Reel + feed post',
                            'tiktok' => 'Short video (60s)',
                        ]),

                    Toggle::make('schedule_instead')
                        ->label('Schedule for later'),

                    DateTimePicker::make('scheduled_at')
                        ->visible(fn($get) => $get('schedule_instead')),
                ]),

            Section::make('Auto-Generated Content Preview')
                ->schema([
                    Tabs::make('previews')
                        ->tabs([
                            Tab::make('YouTube')
                                ->schema([
                                    Placeholder::make('video_preview'),
                                    TextInput::make('youtube_title'),
                                    Textarea::make('youtube_description'),
                                    TagsInput::make('youtube_tags'),
                                ]),

                            Tab::make('Instagram')
                                ->schema([
                                    Placeholder::make('reel_preview'),
                                    Textarea::make('instagram_caption'),
                                ]),

                            // ... more platforms
                        ]),
                ]),
        ]);
    }
}
```

---

## Cost Analysis

### Monthly Costs (for 500 active bloggers)

#### Video Generation
- **OpenAI TTS:** ~$0.015/min → 500 videos × 5min = $37.50/mo
- **Stock Footage (Pexels):** FREE (attribution required)
- **FFmpeg Processing:** FREE (open-source)
- **Storage (S3/Cloudflare R2):** 500 videos × 100MB = 50GB × $0.02 = $1/mo
- **CDN Bandwidth:** 500 videos × 1000 views × 100MB = 50TB × $0.01 = $500/mo

**Video Generation Total:** ~$540/month

#### Alternative: Pictory.ai
- Pro Plan: $47/month (30 videos)
- Would need: 500 videos / 30 = 17 accounts = $800/month
- **Not cost-effective for scale**

#### API Costs (All platforms free tier sufficient)
- YouTube API: FREE (10,000 units/day)
- Instagram Graph API: FREE
- Twitter API: FREE (Basic tier)
- LinkedIn API: FREE
- TikTok API: FREE
- Telegram Bot API: FREE

---

## Revenue Potential from Video Features

### New Tier: "Video Pro" ($49.99/month)
- Unlimited video generation
- All platforms auto-publishing
- Custom branding (intro/outro)
- Priority processing (5min vs 30min)

**Projection with 500 bloggers:**
- 50 upgrade to Video Pro = $2,499/month
- Costs: ~$540/month
- **Net profit: $1,959/month**

---

## Recommended Implementation Phases

### Phase 1 (Week 1-2): Foundation
- [ ] Create database migrations
- [ ] Build `SocialMediaAccount` model and OAuth connections
- [ ] Implement YouTube + Twitter basic publishing
- [ ] Create blogger publishing checklist UI

### Phase 2 (Week 3-4): Video Generation
- [ ] Integrate OpenAI TTS
- [ ] Integrate Pexels API
- [ ] Build FFmpeg video combiner
- [ ] Generate 1080x1920 short videos
- [ ] Test TikTok + Instagram Reels upload

### Phase 3 (Week 5-6): Auto-Publishing
- [ ] Build AI moderator command
- [ ] Implement scheduling system
- [ ] Add all remaining platforms (LinkedIn, Facebook, Telegram)
- [ ] Create analytics dashboard

### Phase 4 (Week 7-8): Polish & Launch
- [ ] Add custom branding options
- [ ] Implement Video Pro subscription tier
- [ ] Create blogger tutorials
- [ ] Launch beta program

---

## My Recommendation

**Start with a hybrid approach:**

1. **Text + Images (Current)** - Keep as is, add cross-posting
2. **Short Videos (60-90s)** - Auto-generate from blog posts using:
   - GPT-4 for script
   - OpenAI TTS for voiceover
   - Pexels for stock footage
   - FFmpeg for combining
3. **Long Videos (5-15min)** - Premium feature, manual or AI-assisted

**Focus platforms:**
- **Phase 1:** YouTube, Twitter, LinkedIn (easiest APIs)
- **Phase 2:** Instagram Reels, TikTok (requires more setup)
- **Phase 3:** Facebook, Telegram, Pinterest

**Blogger checklist should include:**
1. Connect social accounts (OAuth)
2. Review auto-generated content
3. Customize titles/captions
4. One-click publish or schedule

This gives you maximum reach with minimum blogger effort!

Would you like me to start implementing any specific part of this system?
