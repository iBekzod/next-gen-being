# 🔍 Web Scraping System - Complete Guide

## Overview: **NOT Just RSS - Full Web Scraping**

The ContentScraperService supports:
- ✅ **RSS Feed Scraping** (6 auto-detected paths)
- ✅ **Direct HTML Scraping** (with fallback)
- ✅ **Custom CSS Selectors** (per-source configuration)
- ✅ **Content Extraction** (full article + metadata)
- ✅ **Quality Validation** (100+ words minimum)
- ✅ **Content Classification** (tutorial, news, research, article)

---

## 🎯 Two-Tier Scraping System

### **Tier 1: RSS Feed Scraping**

**Automatic Detection:**
```
Tries these common RSS paths:
  /feed/
  /rss/
  /feed.xml
  /rss.xml
  /feed/atom/
  /?feed=rss2
```

**Extracts from RSS:**
```xml
<item>
  <title>Article Title</title>
  <link>https://example.com/article</link>
  <description>Article summary</description>
  <author>Author Name</author>
  <pubDate>2026-01-03</pubDate>
</item>
```

**Sources with Good RSS:**
- ✅ Dev.to
- ✅ TechCrunch
- ✅ Hacker News
- ✅ CSS-Tricks
- ✅ Medium

---

### **Tier 2: Direct HTML Website Scraping**

**Fallback Method:** If RSS not available or fails

**Scraping Process:**
```
1. Fetch website HTML
2. Find article containers using CSS selectors:
   - <article>              (HTML5 semantic)
   - [data-article]         (Data attributes)
   - .article-item          (CSS classes)
   - .post                  (Common class names)
   - .post-item             (Blog post containers)
   - [role="article"]       (ARIA roles)

3. Extract data from each article node:
   - Title from: h1, h2, h3, [data-title], .title
   - Link from: a href
   - Excerpt from: p, [data-summary], .excerpt
   - Author from: [data-author], .author, .by
   - Date from: time, .published, .post-date

4. Fetch full article content
5. Validate quality (100+ words)
6. Detect content type
7. Extract featured image
8. Store in database
```

---

## 📋 Custom CSS Selectors

### **Configure Per-Source:**

```php
// In database:
content_sources.css_selectors (JSON)

Example:
{
  "article_container": ".post-item, article, .entry",
  "title": "h2.post-title, h2 a, .entry-title",
  "content": ".post-content, article > p, .entry-content",
  "author": ".post-author, .author-name",
  "date": ".post-date, time, .published",
  "excerpt": ".post-excerpt, .summary"
}
```

### **How Selectors Are Used:**

```php
// In ContentScraperService:
$crawler->filter($selector)->each(function(Crawler $node) {
    $title = $node->filter('h2.post-title')->first()?->text();
    $content = $node->filter('.post-content')->first()?->html();
    $author = $node->filter('.author-name')->first()?->text();
    // Extract and store...
});
```

---

## 🛠️ How It Works Step-by-Step

### **Full Scraping Workflow:**

```
scrapeSource($source, $limit = 50)
    ↓
Has RSS feed? (Known sources list)
    ↓
YES → scrapeRSSFeed()
       ├─ Guess RSS URL from common paths
       ├─ Fetch RSS/Atom feed
       ├─ Parse XML
       ├─ Extract articles
       └─ Store content
    ↓
NO → scrapeWebsite()
      ├─ Fetch website HTML
      ├─ Find article containers
      ├─ Extract article data
      ├─ Fetch full content from each link
      ├─ Validate quality
      ├─ Detect type
      └─ Store content
    ↓
Mark source as scraped
Return article count
```

---

## 📝 Content Extraction Details

### **1. Article Discovery**

From HTML, find articles using:
```html
<article>...</article>
<div class="post-item">...</div>
<div data-article="true">...</div>
<div role="article">...</div>
```

### **2. Metadata Extraction**

```php
$title = "How to Build a REST API"
$link = "https://example.com/blog/rest-api"
$excerpt = "Learn to build REST APIs..."
$author = "John Doe"
$published_at = "2026-01-03"
```

### **3. Full Content Fetching**

```php
// From the article URL, extract:
✅ Main article content (strips nav/sidebar/ads)
✅ All paragraphs
✅ Code blocks (if tutorial)
✅ Images
✅ Related metadata
```

### **4. Quality Validation**

```php
✅ Minimum 100 words required
✅ Must contain substantial text (not just numbers/dates)
✅ No duplicate URLs
✅ Valid content structure
```

### **5. Content Type Classification**

```php
Tutorial:  "tutorial", "guide", "how to", "step by step"
News:      "breaking", "announce", "launch", "released"
Research:  "research", "study", "paper", "findings"
Article:   Default type
```

### **6. Image Extraction**

```php
// Finds and stores featured image from article
<img src="featured.jpg" alt="...">
```

---

## 🚀 Usage Examples

### **Scrape a Single Source**

```bash
# Via command
php artisan content:scrape-all --limit=50

# Via queue job
ScrapeSingleSourceJob::dispatch($sourceId, 50);
```

### **Manual Service Usage**

```php
use App\Services\ContentScraperService;

$scraper = new ContentScraperService();
$source = ContentSource::find(1);
$articleCount = $scraper->scrapeSource($source, 50);

echo "Found $articleCount articles";
```

### **Custom CSS Selectors**

```php
// Update source with custom selectors
$source->update([
    'css_selectors' => [
        'article_container' => '.blog-post, article.post',
        'title' => 'h2.entry-title, h2.post-title',
        'content' => '.entry-content, .post-content',
        'author' => '.author-name, .by-author',
        'date' => 'time.published, .post-date',
        'excerpt' => '.summary, .excerpt, .entry-excerpt'
    ]
]);
```

---

## 🎯 Supported Websites

### **Built-in RSS Support (Auto-Detected):**
- Dev.to - ✅ Good RSS feed
- TechCrunch - ✅ Good RSS feed
- Hacker News - ✅ Good RSS feed
- CSS-Tricks - ✅ Good RSS feed
- Medium - ✅ Good RSS feed (per-author)

### **Fallback HTML Scraping:**
- Any website with HTML articles
- Medium fallback
- Substack
- Newsletter sites
- News websites
- Technology blogs
- Academic sites

---

## ⚙️ Configuration

### **Per-Source Settings**

```php
ContentSource::create([
    'name' => 'Example Tech Blog',
    'url' => 'https://example.com',
    'category' => 'blog',
    'trust_level' => 90,
    'scraping_enabled' => true,
    'rate_limit_per_sec' => 1,  // Polite crawling
    'css_selectors' => [         // Optional custom selectors
        'article_container' => 'article.post',
        'title' => 'h2.title',
        'content' => '.post-content',
        'author' => '.author',
        'date' => 'time.published',
        'excerpt' => '.excerpt'
    ]
]);
```

### **Global Settings in Service**

```php
private const REQUEST_TIMEOUT = 30;
private const USER_AGENT = 'Mozilla/5.0...';  // Proper bot identification

// Can be adjusted for:
// - Different website speeds
// - Custom user agents
// - Proxy support
// - Authentication
```

---

## 🔄 Retry & Error Handling

### **Failures Handled:**

```php
✅ Network timeouts (30 second timeout)
✅ Invalid RSS feeds (fallback to HTML)
✅ Missing articles (skip gracefully)
✅ Blocked requests (logged, continue)
✅ HTML parsing errors (multiple fallback selectors)
✅ Content validation failures (skip low-quality)
✅ URL extraction failures (relative URL resolution)
```

### **Logging:**

```php
// All scraping activities logged:
Log::info("Successfully scraped TechCrunch, found 45 articles");
Log::warning("RSS feed not found for Dev.to, using HTML fallback");
Log::error("Failed to scrape source: Network timeout");
Log::debug("Selector .post-item failed, trying next selector");
```

---

## 📊 Daily Output Example

### **With Proper Configuration:**

```
06:00 AM - Start scraping

TechCrunch (news)
  ├─ RSS Feed: ✅ Found
  ├─ Articles extracted: 50
  └─ Time: 5 seconds

Dev.to (blog)
  ├─ RSS Feed: ✅ Found
  ├─ Articles extracted: 48
  └─ Time: 4 seconds

CSS-Tricks (blog)
  ├─ RSS Feed: ✅ Found
  ├─ Articles extracted: 42
  └─ Time: 3 seconds

Smashing Magazine (blog)
  ├─ HTML Scraping: ✅ Active
  ├─ Articles extracted: 35
  └─ Time: 8 seconds

[Continue for all 10 sources...]

TOTAL: 450+ articles collected in ~30 minutes
```

---

## 🐛 Troubleshooting

### **Getting 0 Articles?**

**Check:**
1. **Network Access** - Can Docker access external websites?
   ```bash
   docker-compose exec ngb-app curl https://dev.to/feed -I
   ```

2. **CSS Selectors** - Are they correct for the website?
   ```bash
   # Inspect website in browser
   # Update css_selectors in content_sources table
   ```

3. **Rate Limiting** - Increase timeout
   ```php
   private const REQUEST_TIMEOUT = 60; // Was 30
   ```

4. **User Agent** - Some sites block requests
   ```php
   // May need to rotate user agents
   ```

---

## 🚀 Performance Tips

### **Optimize Scraping:**

1. **Batch Processing**
   ```bash
   # Scrape multiple sources in parallel
   ScrapeSingleSourceJob::dispatch(1);
   ScrapeSingleSourceJob::dispatch(2);
   ScrapeSingleSourceJob::dispatch(3);
   ```

2. **Rate Limiting**
   - Respect `rate_limit_per_sec` (default: 1)
   - Polite crawling: 1 request/second per domain
   - Use delays between requests

3. **Caching**
   - Redis caching for RSS feeds
   - Cache parsed HTML for 1 hour
   - Skip already-scraped URLs

4. **Parallel Execution**
   ```bash
   # 10 sources × 1 request/sec = ~50 articles/minute
   # With parallel jobs: 50 articles/minute × 10 workers
   ```

---

## 📚 Code Reference

### **Key Methods:**

```php
ContentScraperService:

public function scrapeSource(ContentSource $source, int $limit = 50): int
  ↓ Main entry point

private function scrapeRSSFeed(ContentSource $source, int $limit): int
  ↓ RSS feed parsing

private function scrapeWebsite(ContentSource $source, int $limit): int
  ↓ HTML website scraping

private function extractArticleData(Crawler $node, ContentSource $source): ?array
  ↓ Extract metadata from HTML

private function fetchFullContent(string $url): string
  ↓ Get complete article content

private function validateContent(string $content): bool
  ↓ Quality validation

private function detectContentType(string $content, string $title): string
  ↓ Classify article type
```

---

## ✅ Summary

### **The System Supports:**

✅ **RSS Feeds** - Auto-detection + fallback
✅ **HTML Scraping** - Direct website scraping with multiple selectors
✅ **Custom Selectors** - Per-source CSS selector configuration
✅ **Content Extraction** - Full article + metadata
✅ **Quality Validation** - 100+ word minimum
✅ **Type Classification** - Tutorial, News, Research, Article
✅ **Image Extraction** - Featured images
✅ **Error Handling** - Graceful fallbacks
✅ **Rate Limiting** - Respectful 1 req/sec
✅ **Duplicate Prevention** - URL deduplication
✅ **Logging** - Complete audit trail

**This is NOT a simple RSS reader - it's a full-featured web scraping system!**

