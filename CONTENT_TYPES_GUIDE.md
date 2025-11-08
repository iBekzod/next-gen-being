# NextGen Being - Content Types Guide

**How Different Content Formats Appear on the Platform**

---

## Content Type Overview

NextGen Being supports **3 types of content**:

1. **Text Blogs** (Traditional) - Articles with images
2. **Visual Stories** (Image-focused) - Instagram/Pinterest style
3. **Video Blogs** (NEW) - Auto-generated videos from text blogs

---

## 1. Text Blogs (Traditional)

### Database Structure

```php
// Post model fields for text blogs
$post = [
    'post_type' => 'article',      // Type identifier
    'title' => 'How to Build a REST API',
    'slug' => 'how-to-build-rest-api',
    'excerpt' => 'Learn to build RESTful APIs...',
    'content' => '<p>Full article content...</p>',
    'featured_image' => '/storage/images/rest-api.jpg',
    'video_url' => null,           // No video
    'video_duration' => null,
    'video_thumbnail' => null,
    'reading_time' => 8,           // Minutes
    'status' => 'published',
];
```

### How It Appears

#### **Home Page Feed:**
```
┌─────────────────────────────────────────────┐
│  [Featured Image]                           │
│  ┌─────────────────────────────────────┐   │
│  │                                     │   │
│  │      REST API Image                 │   │
│  │                                     │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  How to Build a REST API in Laravel        │
│  📖 8 min read  •  Laravel, PHP, API       │
│                                             │
│  Learn to build RESTful APIs with Laravel  │
│  following best practices and modern...    │
│                                             │
│  [Read More →]                              │
└─────────────────────────────────────────────┘
```

#### **Single Post Page:**
```
┌─────────────────────────────────────────────┐
│  How to Build a REST API in Laravel         │
│  By John Doe  •  May 15, 2025               │
│  📖 8 min read  •  Laravel, PHP, API        │
├─────────────────────────────────────────────┤
│  [Featured Image - Full Width]              │
├─────────────────────────────────────────────┤
│                                              │
│  Full Article Content                       │
│  ════════════════════                       │
│                                              │
│  Introduction paragraph...                  │
│                                              │
│  ## Setting Up Laravel                      │
│  Code blocks, images, lists...              │
│                                              │
│  ## Creating Routes                         │
│  More content...                            │
│                                              │
├─────────────────────────────────────────────┤
│  💬 Comments (12)  |  ⬆ Share  |  🔖 Save   │
└─────────────────────────────────────────────┘
```

---

## 2. Visual Stories (Image-Focused)

### Database Structure

```php
// Post model fields for visual stories
$post = [
    'post_type' => 'visual_story',     // Type identifier
    'title' => '10 Beautiful UI Designs',
    'slug' => '10-beautiful-ui-designs',
    'excerpt' => 'Collection of inspiring UI designs',
    'content' => null,                 // Minimal or no text
    'featured_image' => '/storage/images/ui-design-1.jpg',
    'gallery_images' => [              // Multiple images
        '/storage/images/ui-design-1.jpg',
        '/storage/images/ui-design-2.jpg',
        '/storage/images/ui-design-3.jpg',
    ],
    'video_url' => null,
    'status' => 'published',
];
```

### How It Appears

#### **Home Page Feed (Pinterest Style):**
```
┌───────────────┐  ┌───────────────┐  ┌───────────────┐
│               │  │               │  │               │
│   [Image 1]   │  │   [Image 2]   │  │   [Image 3]   │
│               │  │               │  │               │
│               │  │               │  │               │
├───────────────┤  ├───────────────┤  ├───────────────┤
│ 10 Beautiful  │  │ Minimalist    │  │ Dark Mode     │
│ UI Designs    │  │ Website       │  │ Dashboard     │
│ ❤️ 234  💬 12 │  │ ❤️ 189  💬 8  │  │ ❤️ 567  💬 23 │
└───────────────┘  └───────────────┘  └───────────────┘
```

#### **Single Post Page (Gallery View):**
```
┌─────────────────────────────────────────────┐
│  10 Beautiful UI Designs                    │
│  By Jane Smith  •  May 16, 2025             │
│  🎨 Visual Story  •  UI/UX, Design          │
├─────────────────────────────────────────────┤
│  [Image 1 - Full Width]                     │
│  Caption: Modern landing page design        │
├─────────────────────────────────────────────┤
│  [Image 2 - Full Width]                     │
│  Caption: Minimalist dashboard              │
├─────────────────────────────────────────────┤
│  [Image 3 - Full Width]                     │
│  Caption: Mobile app interface              │
├─────────────────────────────────────────────┤
│  ...more images with captions...            │
├─────────────────────────────────────────────┤
│  ❤️ Like (234)  |  ⬆ Share  |  🔖 Save      │
│  💬 Comments (12)                            │
└─────────────────────────────────────────────┘
```

---

## 3. Video Blogs (NEW - Auto-Generated)

### Database Structure

```php
// Post model fields for video blogs
$post = [
    'post_type' => 'article',          // Still an article
    'title' => 'How to Build a REST API',
    'slug' => 'how-to-build-rest-api',
    'excerpt' => 'Learn to build RESTful APIs...',
    'content' => '<p>Full article content...</p>',
    'featured_image' => '/storage/images/rest-api.jpg',

    // Video-specific fields (NEW)
    'video_url' => 'https://storage.../video.mp4',
    'video_duration' => 60,            // Seconds
    'video_thumbnail' => 'https://storage.../thumbnail.jpg',
    'video_captions_url' => 'https://storage.../captions.vtt',

    'reading_time' => 8,
    'status' => 'published',
];

// Related video generation record
$videoGeneration = [
    'post_id' => 1,
    'video_type' => 'tiktok',          // or 'youtube', 'reel', 'short'
    'script' => 'Full video script...',
    'voiceover_url' => 'https://storage.../voiceover.mp3',
    'video_clips' => [...],            // Stock footage used
    'video_url' => 'https://storage.../video.mp4',
    'thumbnail_url' => 'https://storage.../thumbnail.jpg',
    'status' => 'completed',
];
```

### How It Appears

#### **Home Page Feed (Video Highlight):**
```
┌─────────────────────────────────────────────┐
│  [Video Thumbnail with Play Button]        │
│  ┌─────────────────────────────────────┐   │
│  │                                     │   │
│  │         ▶️ PLAY VIDEO               │   │
│  │    REST API Tutorial                │   │
│  │         1:00                        │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  🎥 How to Build a REST API in Laravel     │
│  ⏱️ 1 min video  •  📖 8 min read          │
│  Laravel, PHP, API                          │
│                                             │
│  Learn to build RESTful APIs with Laravel  │
│  Watch the video or read the full guide.   │
│                                             │
│  [▶️ Watch Video] [📖 Read Article]         │
└─────────────────────────────────────────────┘
```

#### **Single Post Page (Video + Article):**
```
┌─────────────────────────────────────────────┐
│  🎥 How to Build a REST API in Laravel      │
│  By John Doe  •  May 15, 2025               │
│  ⏱️ 1 min video  •  📖 8 min read           │
│  Laravel, PHP, API                          │
├─────────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐   │
│  │                                     │   │
│  │   [VIDEO PLAYER]                    │   │
│  │   ▶️ Play | ⏸️ Pause | 🔊 Volume    │   │
│  │   Progress: ═══●════════ 0:45/1:00  │   │
│  │   [CC] Captions  |  ⚙️ Quality      │   │
│  │                                     │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  📹 Also available on:                      │
│  [YouTube] [TikTok] [Instagram] [Twitter]  │
│                                             │
├─────────────────────────────────────────────┤
│  📖 Full Article Below                      │
│  ════════════════════                       │
│                                             │
│  Introduction paragraph...                  │
│  (Full article content same as text blog)  │
│                                             │
├─────────────────────────────────────────────┤
│  💬 Comments (12)  |  ⬆ Share  |  🔖 Save   │
└─────────────────────────────────────────────┘
```

#### **Video-Only View (Embedded Player):**
```
┌─────────────────────────────────────────────┐
│  🎥 How to Build a REST API                 │
│                                             │
│  ┌───────────────────────────────────────┐ │
│  │                                       │ │
│  │    [FULL-SCREEN VIDEO PLAYER]         │ │
│  │                                       │ │
│  │    ▶️ Play/Pause                      │ │
│  │    ═══════●════════ 0:45/1:00         │ │
│  │    🔊 ═══●═══  [CC]  [⚙️]  [⛶]       │ │
│  │                                       │ │
│  │    Caption: Learn to build APIs...    │ │
│  │                                       │ │
│  └───────────────────────────────────────┘ │
│                                             │
│  📖 Read full article                       │
│  ⬇️ Download video                          │
│  ⬆️ Share on social media                   │
└─────────────────────────────────────────────┘
```

---

## Content Type Comparison

### Feature Matrix

| Feature | Text Blog | Visual Story | Video Blog |
|---------|-----------|--------------|------------|
| **Primary Content** | Text + Images | Images | Video + Text |
| **Reading Time** | ✅ Yes | ❌ No | ✅ Yes (video duration) |
| **Featured Image** | ✅ Yes | ✅ Yes (multiple) | ✅ Yes (video thumbnail) |
| **Full Article** | ✅ Yes | ⚠️ Optional | ✅ Yes |
| **Video Player** | ❌ No | ❌ No | ✅ Yes |
| **Auto-Generated** | ❌ No | ❌ No | ✅ Yes (from text) |
| **Social Media** | ⚠️ Manual | ⚠️ Manual | ✅ Auto-publish |
| **Captions/Subtitles** | ❌ No | ❌ No | ✅ Yes (.vtt) |
| **Multi-Platform** | ❌ No | ❌ No | ✅ YouTube, TikTok, etc. |
| **Engagement** | Comments, Likes | Likes, Saves | Views, Comments, Likes |

---

## User Flow Comparison

### Creating Text Blog
```
1. Login to Filament Admin
2. Posts → Create New
3. Fill in:
   - Title
   - Content (rich text editor)
   - Featured image
   - Category, tags
   - SEO settings
4. Publish
5. DONE ✅
```

### Creating Video Blog
```
1. Login to Filament Admin
2. Posts → Create New
3. Fill in:
   - Title
   - Content (rich text editor)
   - Featured image
   - Category, tags
   - SEO settings
4. Publish

5. Generate Video (Automatic or Manual):
   Option A: Automatic (AI Moderator)
   - Cron runs hourly
   - Detects new posts
   - Auto-generates video
   - Auto-publishes to social media

   Option B: Manual
   - Run: php artisan video:generate {post_id} tiktok --queue
   - Monitor progress: /admin/job-statuses
   - Video appears on post when complete

6. DONE ✅ (Post has both text AND video)
```

---

## Post Helper Methods

### Check Content Type

```php
// In Post model

public function isTextBlog(): bool
{
    return $this->post_type === 'article' && !$this->hasVideo();
}

public function isVisualStory(): bool
{
    return $this->post_type === 'visual_story';
}

public function isVideoBlog(): bool
{
    return $this->post_type === 'article' && $this->hasVideo();
}

public function hasVideo(): bool
{
    return !empty($this->video_url);
}

public function getContentTypeLabel(): string
{
    if ($this->isVideoBlog()) {
        return '🎥 Video Blog';
    }

    if ($this->isVisualStory()) {
        return '🎨 Visual Story';
    }

    return '📖 Article';
}
```

---

## Frontend Display Logic

### Blade Template Example

```blade
{{-- resources/views/posts/show.blade.php --}}

<article>
    <header>
        <h1>{{ $post->title }}</h1>
        <div class="meta">
            <span class="content-type">{{ $post->getContentTypeLabel() }}</span>

            @if($post->isVideoBlog())
                <span>⏱️ {{ $post->getFormattedVideoDuration() }} video</span>
                <span>•</span>
            @endif

            @if($post->reading_time)
                <span>📖 {{ $post->reading_time }} min read</span>
            @endif
        </div>
    </header>

    @if($post->hasVideo())
        {{-- Video Player Section --}}
        <div class="video-player">
            <video controls poster="{{ $post->video_thumbnail }}">
                <source src="{{ $post->video_url }}" type="video/mp4">
                @if($post->video_captions_url)
                    <track kind="captions" src="{{ $post->video_captions_url }}"
                           srclang="en" label="English" default>
                @endif
            </video>
        </div>

        {{-- Social Media Links --}}
        @if($post->hasBeenPublishedToSocialMedia())
            <div class="also-available">
                <p>📹 Also available on:</p>
                @foreach($post->socialMediaPosts as $socialPost)
                    @if($socialPost->isPublished())
                        <a href="{{ $socialPost->platform_post_url }}"
                           target="_blank">
                            {{ ucfirst($socialPost->platform) }}
                        </a>
                    @endif
                @endforeach
            </div>
        @endif

        <hr>
        <h2>📖 Full Article</h2>
    @endif

    @if($post->isVisualStory())
        {{-- Gallery View --}}
        <div class="gallery">
            @foreach($post->gallery_images as $image)
                <img src="{{ $image }}" alt="{{ $post->title }}">
            @endforeach
        </div>
    @else
        {{-- Article Content --}}
        <div class="content">
            {!! $post->content !!}
        </div>
    @endif

    <footer>
        {{-- Comments, Share, etc. --}}
    </footer>
</article>
```

---

## Filament Admin View

### Post List (Admin Panel)

```
┌────────────────────────────────────────────────────────────────┐
│  Posts                                                      [+] │
├────────────────────────────────────────────────────────────────┤
│  Filters: [All] [Published] [Draft] [Video Blogs] [Articles]  │
├─────┬──────────────────┬─────────┬────────┬──────────┬────────┤
│ ID  │ Title            │ Type    │ Status │ Video    │ Social │
├─────┼──────────────────┼─────────┼────────┼──────────┼────────┤
│ 1   │ REST API Guide   │ 🎥 Video│ ✅ Pub  │ ✅ Yes   │ 5/5   │
│ 2   │ UI Design Tips   │ 🎨 Story│ ✅ Pub  │ ❌ No    │ 0/0   │
│ 3   │ Laravel Tutorial │ 📖 Text │ ✅ Pub  │ ❌ No    │ 0/0   │
│ 4   │ Vue.js Patterns  │ 🎥 Video│ ⏳ Proc │ ⏳ Proc  │ 0/5   │
└─────┴──────────────────┴─────────┴────────┴──────────┴────────┘

Legend:
- 🎥 Video = Has video generated
- 🎨 Story = Visual story
- 📖 Text = Text-only article
- 5/5 = Published to 5 social platforms
- ⏳ Proc = Video generation in progress
```

### Post Edit Form

```
┌────────────────────────────────────────────────────────────┐
│  Edit Post: REST API Guide                                 │
├────────────────────────────────────────────────────────────┤
│  Basic Information                                         │
│  ─────────────────                                         │
│  Title: [How to Build a REST API in Laravel          ]    │
│  Slug:  [how-to-build-rest-api                       ]    │
│  Content: [Rich Text Editor...]                           │
│                                                            │
│  Media                                                     │
│  ─────                                                     │
│  Featured Image: [Upload]  [rest-api.jpg]                 │
│                                                            │
│  Video (Auto-Generated)                                    │
│  ──────────────────────                                    │
│  ✅ Video Generated                                        │
│  - Video URL: https://storage.../video.mp4                │
│  - Thumbnail: https://storage.../thumbnail.jpg            │
│  - Duration: 1:00                                          │
│  - Generated: May 15, 2025 at 3:45 PM                     │
│  [🎬 Regenerate Video] [🗑️ Delete Video]                  │
│                                                            │
│  Social Media Publishing                                   │
│  ──────────────────────                                    │
│  ✅ YouTube     (Published: https://youtube.com/...)      │
│  ✅ TikTok      (Published: https://tiktok.com/...)       │
│  ✅ Instagram   (Published: https://instagram.com/...)    │
│  ✅ Twitter     (Published: https://twitter.com/...)      │
│  ✅ Telegram    (Published: https://t.me/...)             │
│  [📤 Publish to Social Media]                              │
│                                                            │
│  [Save] [Save & Publish]                                   │
└────────────────────────────────────────────────────────────┘
```

---

## API Response Comparison

### Text Blog API Response

```json
{
  "id": 1,
  "type": "article",
  "title": "How to Build a REST API",
  "slug": "how-to-build-rest-api",
  "excerpt": "Learn to build RESTful APIs...",
  "content": "<p>Full article...</p>",
  "featured_image": "https://domain.com/storage/images/rest-api.jpg",
  "reading_time": 8,
  "has_video": false,
  "video": null,
  "published_at": "2025-05-15T14:30:00Z"
}
```

### Video Blog API Response

```json
{
  "id": 1,
  "type": "article",
  "title": "How to Build a REST API",
  "slug": "how-to-build-rest-api",
  "excerpt": "Learn to build RESTful APIs...",
  "content": "<p>Full article...</p>",
  "featured_image": "https://domain.com/storage/images/rest-api.jpg",
  "reading_time": 8,
  "has_video": true,
  "video": {
    "url": "https://storage.com/videos/1/video.mp4",
    "thumbnail": "https://storage.com/videos/1/thumbnail.jpg",
    "duration": 60,
    "duration_formatted": "1:00",
    "captions_url": "https://storage.com/videos/1/captions.vtt",
    "type": "tiktok",
    "generated_at": "2025-05-15T15:45:00Z"
  },
  "social_media": [
    {
      "platform": "youtube",
      "url": "https://youtube.com/watch?v=...",
      "views": 1234,
      "likes": 89,
      "comments": 12
    },
    {
      "platform": "tiktok",
      "url": "https://tiktok.com/@user/video/...",
      "views": 5678,
      "likes": 234,
      "comments": 45
    }
  ],
  "published_at": "2025-05-15T14:30:00Z"
}
```

---

## SEO Implications

### Text Blog
```html
<!-- Meta tags -->
<title>How to Build a REST API in Laravel</title>
<meta name="description" content="Learn to build RESTful APIs...">
<meta property="og:type" content="article">
<meta property="og:image" content="https://domain.com/images/rest-api.jpg">
```

### Video Blog
```html
<!-- Meta tags -->
<title>How to Build a REST API in Laravel (Video + Article)</title>
<meta name="description" content="Watch the video tutorial or read the full guide...">
<meta property="og:type" content="video.other">
<meta property="og:video" content="https://storage.com/videos/1/video.mp4">
<meta property="og:image" content="https://storage.com/videos/1/thumbnail.jpg">

<!-- Schema.org markup -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "How to Build a REST API in Laravel",
  "description": "Learn to build RESTful APIs...",
  "thumbnailUrl": "https://storage.com/videos/1/thumbnail.jpg",
  "uploadDate": "2025-05-15T15:45:00Z",
  "duration": "PT1M",
  "contentUrl": "https://storage.com/videos/1/video.mp4"
}
</script>
```

---

## User Experience Benefits

### For Text Blogs
- ✅ Traditional reading experience
- ✅ Easy to skim/scan
- ✅ Copy code snippets
- ✅ Searchable content
- ✅ Works without JavaScript

### For Video Blogs
- ✅ **Everything from text blogs** PLUS:
- ✅ Watch quick video summary
- ✅ Multi-platform distribution
- ✅ Higher engagement (video)
- ✅ Accessibility (captions)
- ✅ Mobile-friendly (vertical video)
- ✅ Social media native
- ✅ Choose format (video OR text)

---

## Summary

| Aspect | Text Blog | Video Blog |
|--------|-----------|------------|
| **Creation Effort** | Manual writing | Manual writing + Auto video |
| **Content Format** | Text + Images | Text + Images + Video |
| **Distribution** | Website only | Website + 5 social platforms |
| **Engagement** | Medium | High (video boost) |
| **SEO** | Good | Better (video rich results) |
| **Accessibility** | Text-based | Multi-format (video + text + captions) |
| **User Choice** | Read only | Watch OR read |
| **Reach** | Blog readers | Blog + Social media audiences |

---

## Key Takeaway

**Video blogs are enhanced text blogs:**
- Same article content
- Plus auto-generated video
- Plus social media distribution
- Gives users choice: **Watch 1-minute video OR read 8-minute article**

The platform **doesn't replace** text blogs with videos - it **enhances** them by auto-generating video versions for multi-channel distribution while keeping the full article available.

---

**Best of both worlds!** 🎥 + 📖 = 🚀
