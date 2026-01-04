# 🧪 Complete Testing Guide - Content Curation System

**Last Updated:** 2026-01-03
**System Status:** ✅ 100% Deployed and Ready for Testing
**Environment:** Docker Compose (ngb-app container)

---

## 📋 Quick Start Testing Checklist

```
Phase 1: Collection System
  [ ] Verify sources are initialized
  [ ] Run scraping for single source
  [ ] Check collected_content table for articles
  [ ] Verify article quality (100+ words)

Phase 2: Deduplication System
  [ ] Run duplicate detection
  [ ] Check content_aggregations table
  [ ] Verify grouping by topic
  [ ] Check confidence scores

Phase 3: Paraphrasing System
  [ ] Run paraphrasing for aggregations
  [ ] Check posts table for curated entries
  [ ] Verify fact preservation (60%+ words)
  [ ] Check confidence scores

Phase 4: Translation System
  [ ] Translate to 3 languages (ES, FR, DE)
  [ ] Verify posts table has language variants
  [ ] Check language-specific slugs
  [ ] Verify base post linking

Phase 5: Admin Interface
  [ ] Access Filament admin dashboard
  [ ] Navigate to Content Curation section
  [ ] Verify real-time statistics widget
  [ ] Test filtering and searching

Phase 6: Frontend Components
  [ ] Display curated posts on website
  [ ] Test language switcher
  [ ] Check reference citations
  [ ] Verify tutorial collections
```

---

## 🚀 Phase 1: Collection System - Scraping Content

### Step 1.1: Verify Sources are Initialized

```bash
# Enter Docker container
docker-compose exec ngb-app bash

# Check if sources exist
php artisan tinker

# In tinker:
>>> App\Models\ContentSource::count()
# Expected: 10

>>> App\Models\ContentSource::pluck('name')
# Expected: TechCrunch, Dev.to, Hacker News, CSS-Tricks, etc.

>>> exit()
```

### Step 1.2: Scrape a Single Source (Test Mode)

**Test with limit of 5 articles first:**

```bash
# Run scraping with limit
php artisan content:scrape-all --limit=5

# Expected output:
# Scraping TechCrunch (5 articles)
# Scraping Dev.to (5 articles)
# ...
# Total: 50 articles collected
```

**If you get 0 articles, check these:**

1. **Network connectivity from Docker to external sites:**
   ```bash
   # Inside container, test a specific source
   curl -I https://dev.to/feed 2>&1 | head -5
   ```
   Expected: `HTTP/1.1 200 OK` or `HTTP/2 200`

2. **Custom CSS selectors need tuning:**
   ```bash
   # Update selectors for sources in database
   php artisan tinker

   >>> $source = App\Models\ContentSource::where('name', 'TechCrunch')->first();
   >>> $source->css_selectors = [
   ...     'article_container' => 'article, [data-article], .post-item',
   ...     'title' => 'h2, h3, [data-title], .title',
   ...     'content' => 'article > p, .post-content, main p',
   ...     'author' => '[data-author], .author, .by-line',
   ...     'date' => 'time, .published, .post-date',
   ...     'excerpt' => 'p:first, .excerpt, .summary'
   ... ];
   >>> $source->save();
   >>> exit()
   ```

3. **Try async scraping (background jobs):**
   ```bash
   # Start queue worker first
   php artisan queue:work

   # In another terminal, dispatch async jobs
   php artisan content:scrape-all --async
   ```

### Step 1.3: Verify Collected Content

**Check what was collected:**

```bash
php artisan tinker

# Count collected articles
>>> App\Models\CollectedContent::count()

# View recent articles
>>> App\Models\CollectedContent::latest()->take(5)->get()->map(function($c) {
...     return [
...         'id' => $c->id,
...         'title' => $c->title,
...         'source' => $c->contentSource->name,
...         'words' => str_word_count($c->full_content),
...         'type' => $c->content_type
...     ];
... });

# Check specific source's articles
>>> $source = App\Models\ContentSource::where('name', 'Dev.to')->first();
>>> $source->collectedContent()->count()
>>> $source->collectedContent()->take(3)->get();

>>> exit()
```

**Expected results:**
```
- Total collected articles: 40-50+
- Each article: 100+ words
- Content types: tutorial, news, research, article
- Authors populated: Yes
- Published dates: Yes
```

---

## 🔄 Phase 2: Deduplication System - Grouping Content

### Step 2.1: Run Duplicate Detection

```bash
# Find duplicates and group similar content
php artisan content:deduplicate --hours=24

# Expected output:
# Found X related articles
# Created Y aggregations
# Confidence scores: 0.75-0.95
```

### Step 2.2: Check Aggregations Created

```bash
php artisan tinker

# Count aggregations
>>> App\Models\ContentAggregation::count()
# Expected: 10-20 (depending on how many articles collected)

# View aggregation details
>>> App\Models\ContentAggregation::with(['collectedContent', 'contentSources'])->take(5)->get()->map(function($agg) {
...     return [
...         'id' => $agg->id,
...         'topic' => $agg->topic,
...         'sources' => $agg->contentSources->pluck('name')->join(', '),
...         'articles' => $agg->collectedContent->count(),
...         'confidence' => round($agg->confidence_score * 100) . '%'
...     ];
... });

# Verify grouping is working
>>> App\Models\ContentAggregation::where('confidence_score', '>=', 0.75)->count()
# Expected: 8+ (75%+ confidence matches)

>>> exit()
```

**Expected results:**
```
- Aggregations created: 10-20
- Each aggregation has 2-5 similar articles
- Confidence scores: 0.75-0.95 (75-95%)
- Topics detected: e.g., "React", "AI", "DevOps", etc.
```

### Step 2.3: Verify TF-IDF Algorithm

```bash
php artisan tinker

# Check a specific aggregation
>>> $agg = App\Models\ContentAggregation::first();
>>> $agg->load('collectedContent');
>>> $agg->collectedContent->pluck('title');

# Check similarity score calculation
>>> $agg->confidence_score
# Expected: 0.75-1.0 for related articles

>>> exit()
```

---

## 📝 Phase 3: Paraphrasing System - Creating Curated Posts

### Step 3.1: Run Paraphrasing

**IMPORTANT:** This requires `ANTHROPIC_API_KEY` in `.env`:

```bash
# Check if API key is set
grep ANTHROPIC_API_KEY .env

# If not set, add it:
echo "ANTHROPIC_API_KEY=your-key-here" >> .env

# Run paraphrasing (async recommended for speed)
php artisan content:paraphrase-pending --limit=5 --async

# Or run synchronously (takes longer)
php artisan content:paraphrase-pending --limit=5
```

### Step 3.2: Monitor Paraphrasing Job

```bash
# If running async, watch queue worker
docker-compose logs -f ngb-app

# You should see:
# Processing job ParaphraseAggregationJob
# [OK] Paraphrased aggregation #1
# [OK] Created curated post #42
```

### Step 3.3: Verify Curated Posts Created

```bash
php artisan tinker

# Check for curated posts
>>> $curated = App\Models\Post::where('is_curated', true)->get();
>>> $curated->count()
# Expected: 5 (or more if you ran multiple times)

# Check curated post details
>>> $post = $curated->first();
>>> [
...     'id' => $post->id,
...     'title' => $post->title,
...     'is_curated' => $post->is_curated,
...     'confidence_score' => $post->paraphrase_confidence_score,
...     'fact_verified' => $post->is_fact_verified,
...     'sources' => count($post->source_ids ?? []),
...     'word_count' => str_word_count($post->content)
... ];

# Verify fact preservation
>>> $post->paraphrase_confidence_score
# Expected: 0.60-0.95 (60-95% fact preservation)

# Check source linking
>>> $post->source_ids
# Expected: [1, 3, 5] or similar array of source IDs

>>> exit()
```

**Expected results:**
```
- Curated posts created: 5+
- Content: Human-level paraphrased (not AI-generic)
- Fact preservation: 60%+ word match
- Confidence scores: 0.60-0.95
- Sources linked: 2+ sources per post
```

### Step 3.4: Verify Content Quality

```bash
php artisan tinker

# Check a curated post's elaboration
>>> $post = App\Models\Post::where('is_curated', true)->first();
>>> echo $post->content;

# Should be:
# - More detailed than original summaries
# - Human-written style
# - Multiple paragraphs
# - References to importance/use cases
# - NOT generic AI content

>>> exit()
```

---

## 🌍 Phase 4: Translation System - Multi-Language Support

### Step 4.1: Translate Curated Posts

**Requires API key:**

```bash
# Translate curated posts to Spanish, French, German
php artisan content:translate-pending --languages=es,fr,de --limit=3

# Expected output:
# Translating posts to ES...
# [OK] Post #42 → Spanish (es)
# [OK] Post #42 → French (fr)
# [OK] Post #42 → German (de)
```

### Step 4.2: Verify Translations Created

```bash
php artisan tinker

# Check translation count
>>> App\Models\Post::where('base_post_id', '!=', null)->count()
# Expected: 9 (3 posts × 3 languages)

# View language variants of a post
>>> $basePost = App\Models\Post::where('is_curated', true)->first();
>>> $translations = App\Models\Post::where('base_post_id', $basePost->id)->get();
>>> $translations->pluck('base_language');
# Expected: ['es', 'fr', 'de']

# Check language-specific slugs
>>> $translations->map(function($post) {
...     return [
...         'language' => $post->base_language,
...         'slug' => $post->slug
...     ];
... });
# Expected: [
#     ['language' => 'es', 'slug' => 'titulo-del-post-es'],
#     ['language' => 'fr', 'slug' => 'titre-du-post-fr'],
#     ...
# ]

>>> exit()
```

**Expected results:**
```
- Translations created: 3 languages per post
- Language field populated: es, fr, de, etc.
- Slugs language-specific: yes (suffix or prefix)
- Base post linked: yes (base_post_id populated)
- Content translated: yes (readable in each language)
```

### Step 4.3: Verify Language Switcher Data

```bash
php artisan tinker

# Test language switcher service
>>> $service = app(\App\Services\TranslationService::class);
>>> $post = App\Models\Post::where('is_curated', true)->first();
>>> $switcher = $service->getLanguageSwitcherData($post);
>>> $switcher;

# Expected output:
# [
#     'current' => ['language' => 'en', 'label' => 'English', 'url' => '/posts/post-title'],
#     'available' => [
#         ['language' => 'es', 'label' => 'Español', 'url' => '/es/posts/titulo-del-post'],
#         ['language' => 'fr', 'label' => 'Français', 'url' => '/fr/posts/titre-du-post'],
#         ...
#     ]
# ]

>>> exit()
```

---

## 🎛️ Phase 5: Admin Interface Testing

### Step 5.1: Access Filament Admin

**In your browser:**

```
http://localhost:9070/admin
```

Login with admin credentials (set in your `.env`):
```
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=password
```

### Step 5.2: Verify Content Curation Navigation Group

In the left sidebar, you should see:

```
📦 Content Curation
  ├─ 📊 Content Sources
  ├─ 📄 Collected Content
  ├─ 🔗 Content Aggregations
  ├─ ✨ Curated Posts
  ├─ 📚 Source References
  └─ 🎓 Tutorial Collections
```

### Step 5.3: Test Content Sources Management

**Navigate to:** Content Curation → Content Sources

**Verify:**
```
✅ List shows 10 sources (TechCrunch, Dev.to, etc.)
✅ Each source has:
   - Name
   - URL
   - Category
   - Trust Level (slider 0-100)
   - Scraping Enabled (toggle)
   - Last Scraped timestamp

✅ Click on a source to edit:
   - Update CSS selectors
   - Adjust trust level
   - Test scraping button
   - View article count
```

### Step 5.4: Test Collected Content Management

**Navigate to:** Content Curation → Collected Content

**Verify:**
```
✅ List shows all articles collected
✅ Filtering works:
   - By Source
   - By Content Type (tutorial, news, research, article)
   - By Status (processed/unprocessed)
   - By Duplicate status

✅ Search works (by title)
✅ Can view full content of article
✅ Can mark as duplicate manually
```

### Step 5.5: Test Content Aggregations

**Navigate to:** Content Curation → Content Aggregations

**Verify:**
```
✅ Shows grouped topics
✅ Each aggregation displays:
   - Topic name
   - Sources grouped (with count)
   - Articles count
   - Confidence score (%)
   - Created date

✅ Can click "Create Post" to paraphrase
✅ Can view related articles
```

### Step 5.6: Test Curated Posts Management (Main Interface)

**Navigate to:** Content Curation → Curated Posts

**This is the PRIMARY admin interface. Verify:**

```
✅ List view shows:
   - Post title
   - Created date
   - Curated indicator ✓
   - Confidence score (%)
   - Fact verified badge
   - Language count
   - Source count

✅ Edit view allows:
   - Verify facts (checkbox)
   - Add verification notes
   - View paraphrase confidence
   - Manage language versions
   - Quick translate button
   - View source references
   - Bulk publish toggle

✅ Can filter by:
   - Curated posts only
   - Language
   - Fact verification status
   - Confidence score range

✅ Can sort by:
   - Confidence score
   - Created date
   - Fact verified status
```

### Step 5.7: Test Source References Management

**Navigate to:** Content Curation → Source References

**Verify:**
```
✅ List shows all citations
✅ Each reference displays:
   - Title
   - Domain
   - Author
   - Published date
   - Citation style
   - Position in post

✅ Can filter by:
   - Domain
   - Citation format
   - Date range

✅ Edit view allows:
   - Change citation format (APA, Chicago, Harvard, Inline)
   - Copy citation to clipboard
   - View BibTeX format
```

### Step 5.8: Test Dashboard Widget

**Navigate to:** Admin Dashboard (home)

**Verify Content Curation Stats Widget:**

```
✅ Shows real-time statistics:
   - Total sources configured: 10
   - Articles collected: X
   - Aggregations pending: Y
   - Curated posts: Z
   - Average confidence: X%
   - Fact verified: Y%

✅ Pipeline status indicator showing:
   - Collection status
   - Deduplication status
   - Paraphrasing status
   - Translation status
```

---

## 🎨 Phase 6: Frontend Components Testing

### Step 6.1: Display Curated Posts on Website

**In your Blade template:**

```blade
@foreach($curatedPosts as $post)
    @include('components.curated-post-card', ['post' => $post])
@endforeach
```

**Verify component displays:**
```
✅ Post title and excerpt
✅ Confidence score badge (colored: red/yellow/green)
✅ Fact verified checkmark
✅ Top 3 sources list
✅ Language badges (EN, ES, FR, DE, etc.)
✅ Read more button
✅ Responsive design (mobile/tablet/desktop)
```

### Step 6.2: Test Language Switcher

**In your post view:**

```blade
@include('components.language-switcher', ['post' => $post])
```

**Verify component:**
```
✅ Shows current language with flag
✅ Dropdown lists available languages
✅ Language names in native language (Español, Français, etc.)
✅ Clicking switches to that language version
✅ URL updates correctly
✅ Respects browser language preference
```

### Step 6.3: Test References Display

**In your post view:**

```blade
@include('components.post-references', ['post' => $post])
```

**Verify component:**
```
✅ Shows references section
✅ Groups references by domain
✅ Each reference shows:
   - Title
   - Author
   - Published date
   - Citation format selector

✅ Format switching:
   - APA: "Author, A. (2026). Title. Domain."
   - Chicago: "Author. \"Title.\" Domain, 2026."
   - Harvard: "Author, 2026. Title. [Domain]"
   - Inline: "Author (2026)"

✅ Copy citation to clipboard
✅ Export bibliography as:
   - PDF
   - BibTeX
   - Plain text
```

### Step 6.4: Test Tutorial Collections Display

**In your tutorials page:**

```blade
@foreach($tutorials as $tutorial)
    @include('components.tutorial-collection-card', ['tutorial' => $tutorial])
@endforeach
```

**Verify component:**
```
✅ Shows tutorial title
✅ Displays skill level (Beginner, Intermediate, Advanced)
✅ Shows estimated hours
✅ Displays reading time
✅ Shows all contributing sources
✅ Lists step count
✅ Shows code example count
✅ Displays best practices summary
✅ Shows common pitfalls
✅ Read full tutorial button
✅ Responsive design
```

---

## 📊 Phase 7: End-to-End Pipeline Testing

### Complete Workflow Test

Run through the entire pipeline in sequence:

```bash
# Step 1: Initialize sources
php artisan content:init-sources

# Step 2: Scrape all sources (5 articles each)
php artisan content:scrape-all --limit=5

# Verify articles collected
php artisan tinker
>>> App\Models\CollectedContent::count()
>>> exit()

# Step 3: Find duplicates (async)
php artisan content:deduplicate --hours=24 --async

# Step 4: Paraphrase aggregations
php artisan content:paraphrase-pending --limit=3 --async

# Step 5: Translate to multiple languages
php artisan content:translate-pending --languages=es,fr,de --limit=3

# Step 6: Extract references
php artisan content:prepare-review --limit=3

# Step 7: Verify complete pipeline
php artisan tinker
>>> echo "Collected: " . App\Models\CollectedContent::count() . "\n";
>>> echo "Aggregations: " . App\Models\ContentAggregation::count() . "\n";
>>> echo "Curated Posts: " . App\Models\Post::where('is_curated', true)->count() . "\n";
>>> echo "Translations: " . App\Models\Post::where('base_post_id', '!=', null)->count() . "\n";
>>> echo "References: " . App\Models\SourceReference::count() . "\n";
>>> exit()
```

**Expected results:**
```
Collected: 40-50 articles
Aggregations: 10-15 grouped topics
Curated Posts: 3+ original posts
Translations: 9+ (3 posts × 3 languages)
References: 30+ citations
```

---

## 🐛 Troubleshooting

### Issue: No Articles Collected (0 articles)

**Diagnosis steps:**

```bash
# 1. Check network access
docker-compose exec ngb-app curl -I https://dev.to/feed

# 2. Check source URLs are valid
php artisan tinker
>>> App\Models\ContentSource::pluck('url')
>>> exit()

# 3. Check CSS selectors are configured
php artisan tinker
>>> $source = App\Models\ContentSource::first();
>>> $source->css_selectors
>>> exit()

# 4. Enable debug logging
# In .env: LOG_LEVEL=debug
# Check: storage/logs/laravel-*.log

# 5. Test RSS feed directly
docker-compose exec ngb-app php -r "
    \$rss = @file_get_contents('https://dev.to/feed');
    echo \$rss ? 'RSS OK' : 'RSS FAILED';
"
```

**Solutions:**

```bash
# Update CSS selectors manually
php artisan tinker

>>> $source = App\Models\ContentSource::where('name', 'Dev.to')->first();
>>> $source->update([
...     'css_selectors' => [
...         'article_container' => 'article, .crayons-story',
...         'title' => 'h2, h1, .crayons-story__title',
...         'content' => 'article p, .crayons-story__body p',
...         'author' => '.profile-preview__name, .user-profile-link',
...         'date' => 'time, .published-at',
...         'excerpt' => 'p:first-of-type, .crayons-story__description'
...     ]
... ]);

>>> exit()
```

### Issue: Articles with Low Word Count

**Check quality validation:**

```bash
php artisan tinker

# Find articles under 100 words
>>> $lowQuality = App\Models\CollectedContent::whereRaw('LENGTH(full_content) < 500')->get();
>>> $lowQuality->count()

# These should be filtered out - check if they exist
>>> if($lowQuality->count() > 0) {
...     echo "Found " . $lowQuality->count() . " low-quality articles\n";
... }

>>> exit()
```

### Issue: Low Confidence Scores (< 60%)

**Check paraphrasing quality:**

```bash
php artisan tinker

# View low confidence posts
>>> $lowConf = App\Models\Post::where('is_curated', true)
...                            ->where('paraphrase_confidence_score', '<', 0.6)
...                            ->get();

>>> if($lowConf->count() > 0) {
...     echo "Found " . $lowConf->count() . " low confidence posts\n";
...     // These need manual review
... }

>>> exit()
```

### Issue: Translations Not Working

**Check API key and language configuration:**

```bash
# Verify API key is set
grep ANTHROPIC_API_KEY .env

# Check translation queue
php artisan queue:failed

# Retry failed translation jobs
php artisan queue:retry all
```

---

## ✅ Testing Checklist Summary

Use this checklist to track your testing progress:

```
PHASE 1: COLLECTION
  [ ] 10 sources initialized
  [ ] Scraping works (at least 5 articles)
  [ ] Articles have 100+ words
  [ ] Content types detected correctly
  [ ] Authors and dates populated

PHASE 2: DEDUPLICATION
  [ ] Duplicates detected (10+)
  [ ] Aggregations created (5+)
  [ ] Confidence scores 75%+
  [ ] Topics properly grouped
  [ ] No false duplicates

PHASE 3: PARAPHRASING
  [ ] Curated posts created (3+)
  [ ] Content elaborated (more detailed than summary)
  [ ] Fact preservation 60%+ (confidence score)
  [ ] Human-like writing style
  [ ] Multiple sources linked per post

PHASE 4: TRANSLATION
  [ ] Translated to 3+ languages
  [ ] Language slugs unique per language
  [ ] Base post correctly linked
  [ ] All translations readable
  [ ] Confidence maintained across languages

PHASE 5: ADMIN INTERFACE
  [ ] Content Curation section accessible
  [ ] All 6 resources visible
  [ ] Dashboard widget shows stats
  [ ] Filtering works
  [ ] Search functionality works
  [ ] Edit/update operations work

PHASE 6: FRONTEND
  [ ] Curated post cards display
  [ ] Language switcher works
  [ ] References display correctly
  [ ] Tutorial cards show complete info
  [ ] Components responsive on mobile

PHASE 7: END-TO-END
  [ ] Full pipeline runs without errors
  [ ] All tables populated with data
  [ ] No database constraint violations
  [ ] Performance acceptable
  [ ] Logging shows no critical errors
```

---

## 🚀 Performance Expectations

### Collection Phase
- **Expected time:** ~2-5 minutes for 50 articles (1 sec/article with rate limiting)
- **Expected output:** 40-50 articles from 10 sources
- **Network dependent:** Yes (requires internet access from Docker)

### Deduplication Phase
- **Expected time:** ~30 seconds for 50 articles
- **TF-IDF calculation:** O(n²) complexity, manageable for <500 articles
- **Expected aggregations:** 10-20 groups from 50 articles

### Paraphrasing Phase
- **Expected time:** ~30 seconds per post (API call dependent)
- **Confidence requirement:** 60%+ minimum
- **Expected posts:** 1 per 2-3 aggregations (some groups too similar to separate)

### Translation Phase
- **Expected time:** ~30 seconds per language per post
- **3 languages × 3 posts:** ~5 minutes total
- **API call dependent:** Yes

### Total Pipeline Time
- **Without errors:** ~10-15 minutes for full cycle (50 articles → 3+ curated posts → 9+ translations)
- **Parallelized with queue workers:** Can reduce to 5-7 minutes

---

## 📞 Support & Debugging

### Enable Debug Mode

```bash
# In .env
APP_DEBUG=true
LOG_LEVEL=debug

# Restart container
docker-compose restart ngb-app

# Watch logs in real-time
docker-compose logs -f ngb-app
```

### View Queue/Job Errors

```bash
# Failed jobs
php artisan queue:failed

# Retry specific job
php artisan queue:retry {job-id}

# Flush all failed jobs
php artisan queue:flush
```

### Database Direct Query

```bash
# Connect to PostgreSQL
docker-compose exec ngb-database psql -U laravel -d nextgenbeing

# Basic queries:
SELECT COUNT(*) FROM content_sources;
SELECT COUNT(*) FROM collected_content;
SELECT COUNT(*) FROM content_aggregations;
SELECT COUNT(*) FROM posts WHERE is_curated = true;
SELECT COUNT(*) FROM source_references;

\q  # Exit
```

---

## ✨ Success Criteria

### System is working correctly when:

1. ✅ **Collection**: 40+ articles collected from 10 sources with 100+ word minimum
2. ✅ **Deduplication**: 10+ aggregations with 75%+ confidence scores
3. ✅ **Paraphrasing**: 3+ curated posts with 60%+ fact preservation
4. ✅ **Translation**: Each curated post translated to 3+ languages
5. ✅ **References**: 30+ source citations with multiple format support
6. ✅ **Admin**: All features accessible and working in Filament
7. ✅ **Frontend**: Components display correctly and are responsive
8. ✅ **Performance**: Full pipeline completes in <15 minutes
9. ✅ **Quality**: Content is human-level paraphrased, not generic
10. ✅ **Attribution**: All sources properly tracked and cited

---

## 🎉 Testing Complete

Once you've verified all phases:

1. The content curation system is **100% operational**
2. Ready for **production deployment**
3. All **quality gates are functioning**
4. **Admin interface is accessible**
5. **Frontend components are integrated**

You can then proceed with:
- Fine-tuning CSS selectors for specific sites
- Scheduling automated scraping via scheduler
- Running 24/7 content collection
- Publishing curated posts automatically
- Managing multi-language versions

---

**Generated:** 2026-01-03
**For System:** Next-Gen Being Content Curation
**Status:** Ready for Testing
