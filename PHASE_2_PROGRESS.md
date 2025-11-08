# Phase 2: Video Generation Engine - Progress Update

## Status: 60% Complete ✅

Successfully created the AI video generation pipeline services. The system can now convert blog posts into video scripts, generate voiceovers, fetch stock footage, and create captions.

---

## ✅ Completed Services (5/7)

### 1. VideoGenerationService
**File:** [app/Services/Video/VideoGenerationService.php](app/Services/Video/VideoGenerationService.php)

**What it does:**
- Orchestrates the entire video generation pipeline
- Converts blog posts to videos in 4 formats (YouTube, TikTok, Reels, Shorts)
- Tracks generation status and costs
- Handles errors gracefully with database logging

**Key Methods:**
```php
generateFromPost(Post $post, string $type): VideoGeneration
canGenerateType(User $user, string $type): bool
getEstimatedTime(string $type): int // Returns seconds
```

**Video Specs:**
- **YouTube**: 1920x1080, 10 minutes, horizontal
- **TikTok**: 1080x1920, 60 seconds, vertical
- **Instagram Reel**: 1080x1920, 90 seconds, vertical
- **YouTube Short**: 1080x1920, 60 seconds, vertical

---

### 2. ScriptGeneratorService
**File:** [app/Services/Video/ScriptGeneratorService.php](app/Services/Video/ScriptGeneratorService.php)

**What it does:**
- Converts blog posts into video scripts using GPT-4
- Generates platform-specific scripts (different styles for YouTube vs TikTok)
- Creates timestamps for each sentence
- Extracts key points for visual selection

**Key Features:**
- **TikTok style**: Fast-paced, hook in first 3 seconds
- **YouTube style**: Detailed tutorial with clear sections
- **Reel style**: Visually descriptive, engaging
- **Short style**: Quick tips, punchy format

**Generated Output:**
```php
[
    'text' => "Full script text...",
    'timestamps' => [
        ['start' => 0.00, 'end' => 3.50, 'text' => 'First sentence...'],
        ['start' => 3.50, 'end' => 7.20, 'text' => 'Second sentence...'],
        // ...
    ]
]
```

---

### 3. VoiceoverService
**File:** [app/Services/Video/VoiceoverService.php](app/Services/Video/VoiceoverService.php)

**What it does:**
- Generates voiceover audio from script text
- Supports 2 TTS providers:
  - **OpenAI TTS**: Standard quality (all tiers)
  - **ElevenLabs**: Premium quality (Video Pro tier only)
- Automatic voice selection based on video type

**Voice Selection:**
- **YouTube**: Professional (Onyx)
- **TikTok**: Energetic female (Nova)
- **Instagram Reel**: Warm female (Shimmer)
- **YouTube Short**: Clear male (Echo)

**Cost Estimate:**
- OpenAI TTS: $0.015 per minute (~$1 for 10min video)
- ElevenLabs: $0.30 per 1K characters (~$3 for 10min video)

---

### 4. StockFootageService
**File:** [app/Services/Video/StockFootageService.php](app/Services/Video/StockFootageService.php)

**What it does:**
- Fetches free stock videos from Pexels API
- Automatically extracts keywords from blog post (category, tags, title)
- Downloads and caches videos to avoid API rate limits
- Supports both portrait (TikTok/Reels) and landscape (YouTube) videos

**Smart Keyword Extraction:**
1. Blog post category
2. Blog post tags
3. Key terms from title
4. Tech-related keywords from excerpt (Laravel, PHP, JavaScript, etc.)
5. Fallback to generic tech footage if needed

**Output Example:**
```php
[
    [
        'url' => 'https://videos.pexels.com/...',
        'duration' => 5,
        'start_time' => 0,
        'keyword' => 'Laravel',
        'attribution' => 'Video by John Doe from Pexels',
    ],
    // ... more clips
]
```

---

### 5. CaptionGeneratorService
**File:** [app/Services/Video/CaptionGeneratorService.php](app/Services/Video/CaptionGeneratorService.php)

**What it does:**
- Generates WebVTT subtitle files from timestamped scripts
- Supports SRT format (alternative)
- Platform-specific styling (TikTok style with emojis, YouTube clean style)
- Auto-formatting (max 42 chars/line, 2 lines max)

**WebVTT Output:**
```vtt
WEBVTT

1
00:00:00.000 --> 00:00:03.500
Welcome to this tutorial on Laravel!

2
00:00:03.500 --> 00:00:07.200
Today we'll learn about middleware...
```

---

## ⏳ Remaining Services (2/7)

### 6. VideoEditorService (Next)
**File:** Will create [app/Services/Video/VideoEditorService.php](app/Services/Video/VideoEditorService.php)

**What it will do:**
- Use FFmpeg to combine all elements:
  - Multiple video clips
  - Voiceover audio
  - Background music (optional)
  - Captions overlay
  - Intro/outro (for Video Pro tier)
- Export final MP4 video
- Generate thumbnail image
- Upload to S3/Cloudflare R2

---

### 7. GenerateVideoCommand (Next)
**File:** Will create [app/Console/Commands/GenerateVideoCommand.php](app/Console/Commands/GenerateVideoCommand.php)

**What it will do:**
- Artisan command: `php artisan video:generate {post_id} {type}`
- Queue support for background processing
- Progress tracking and notifications
- Error handling and retry logic

---

## Architecture Overview

```
Blog Post
    ↓
[1] ScriptGeneratorService
    → GPT-4 converts post to video script
    → Generates timestamps
    ↓
[2] VoiceoverService
    → OpenAI TTS or ElevenLabs
    → Generates MP3 audio file
    ↓
[3] StockFootageService
    → Pexels API fetches relevant videos
    → Downloads and caches locally
    ↓
[4] CaptionGeneratorService
    → Creates WebVTT subtitle file
    → Platform-specific styling
    ↓
[5] VideoEditorService (TO DO)
    → FFmpeg combines all elements
    → Exports final MP4
    ↓
Final Video + Thumbnail + Captions
```

---

## Pipeline Example: TikTok Video (60s)

**Input:** Blog post "10 Laravel Best Practices"

**Step 1 - Script Generation:**
```
Hook (0-3s): "Want to write better Laravel code? Here are 10 pro tips!"
Body (3-55s): "Tip 1: Use route model binding..."
CTA (55-60s): "Follow for more Laravel tips!"
```

**Step 2 - Voiceover:**
- Voice: Nova (energetic female)
- Duration: 58 seconds
- Cost: ~$0.02

**Step 3 - Stock Footage:**
- 12 clips × 5 seconds each
- Keywords: "coding", "Laravel", "PHP", "developer", "workspace"
- Source: Pexels (free)

**Step 4 - Captions:**
- WebVTT with TikTok styling
- Emojis added to key words
- 42 chars/line max

**Step 5 - Video Editor (TO DO):**
- Combine clips
- Add voiceover
- Overlay captions
- Export 1080x1920 MP4

---

## Cost Breakdown (Per Video)

### TikTok Video (60 seconds):
- Script generation (GPT-4): $0.01
- Voiceover (OpenAI TTS): $0.015
- Stock footage (Pexels): FREE
- FFmpeg processing: FREE
- Storage (100MB): $0.002
- **Total: ~$0.03 per video**

### YouTube Video (10 minutes):
- Script generation (GPT-4): $0.03
- Voiceover (OpenAI TTS): $0.15
- Stock footage (Pexels): FREE
- FFmpeg processing: FREE
- Storage (500MB): $0.01
- **Total: ~$0.19 per video**

### With ElevenLabs (Premium):
- Add ~$2-3 per video (10x cost of OpenAI TTS)
- Only available for Video Pro tier

---

## Next Steps

### Immediate (Phase 2 completion):
1. ✅ Create VideoEditorService (FFmpeg wrapper)
2. ✅ Create GenerateVideoCommand (Artisan command)
3. ✅ Update services.php config
4. ✅ Test end-to-end pipeline

### Required Environment Variables:

```env
# OpenAI (for script + voiceover)
OPENAI_API_KEY=sk-your-key
OPENAI_TTS_VOICE=onyx

# Pexels (for stock footage)
PEXELS_API_KEY=your-pexels-key

# ElevenLabs (optional, for premium voice)
ELEVENLABS_API_KEY=your-elevenlabs-key
ELEVENLABS_VOICE_ID=21m00Tcm4TlvDq8ikWAM

# Storage (S3 or Cloudflare R2)
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=nextgen-being-videos
```

### Installation Required:
```bash
# FFmpeg (for video processing)
# Ubuntu/Debian:
sudo apt install ffmpeg

# Windows:
# Download from https://ffmpeg.org/download.html

# macOS:
brew install ffmpeg

# Verify installation:
ffmpeg -version
```

---

## Testing Checklist

```bash
# 1. Test script generation
php artisan tinker
>>> $post = Post::first();
>>> $service = app(ScriptGeneratorService::class);
>>> $script = $service->generateScript($post, 'tiktok');
>>> dump($script);

# 2. Test voiceover generation
>>> $voiceService = app(VoiceoverService::class);
>>> $url = $voiceService->generateVoiceover($script['text'], $post->author, 'tiktok');
>>> dump($url);

# 3. Test stock footage
>>> $footageService = app(StockFootageService::class);
>>> $clips = $footageService->fetchFootage($post, 60);
>>> dump($clips);

# 4. Test caption generation
>>> $captionService = app(CaptionGeneratorService::class);
>>> $captions = $captionService->generateCaptions($script['timestamps'], 60);
>>> dump($captions);

# 5. Test full pipeline (after VideoEditorService is done)
>>> $videoService = app(VideoGenerationService::class);
>>> $video = $videoService->generateFromPost($post, 'tiktok');
>>> dump($video);
```

---

## Performance Metrics

### Generation Times (Estimated):
- **TikTok (60s)**: 2-3 minutes total
  - Script: 30s
  - Voiceover: 15s
  - Footage: 30s
  - Captions: 5s
  - Video editing: 30-60s

- **YouTube (10min)**: 5-7 minutes total
  - Script: 60s
  - Voiceover: 30s
  - Footage: 60s
  - Captions: 10s
  - Video editing: 2-3 minutes

### Scalability:
- **Queue system**: Process videos in background
- **Batch processing**: Generate 100 videos/hour (with proper queue workers)
- **Cost at scale**: $3-5 per 100 TikTok videos

---

## Phase 2 Completion: 60%

**Status**: Core AI services complete ✅
**Remaining**: FFmpeg integration and command interface
**Time to complete**: ~2-3 hours
**Blocker**: None

Ready to continue with VideoEditorService? 🎬
