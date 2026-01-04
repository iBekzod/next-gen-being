# Content Curation Strategy - Implementation Plan

**Date:** 2026-01-02
**Status:** Planning Phase
**Strategy:** From AI Generation → Curated Sourced Content

---

## 🎯 Strategy Overview

Instead of generating content from scratch, the platform will:
1. **Collect** diverse technology content from whitelisted sources (news, blogs, research)
2. **Aggregate** similar topics from multiple sources to avoid duplication
3. **Paraphrase** using Claude with source preservation (not generation)
4. **Translate** to multiple languages for global reach
5. **Elaborate** through human-style paraphrasing to explain importance and context
6. **Cite** all sources with proper attribution as a tech blogger would
7. **Verify** facts through whitelisted sources only

---

## 📋 Content Types & Sources

### Source Categories
| Category | Examples | Priority |
|----------|----------|----------|
| **Tech News** | TechCrunch, The Verge, Wired, VentureBeat | High |
| **Developer Blogs** | Dev.to, CSS-Tricks, Smashing Magazine, Medium (verified) | High |
| **Research** | ArXiv, ResearchGate, Papers with Code, Google Research | Medium |
| **Trending** | Hacker News, Product Hunt, Reddit r/technology | Medium |
| **Brand News** | Official tech company blogs, announcements | High |
| **Inventions/High-Tech** | IEEE Spectrum, Nature Tech, MIT Technology Review | Medium |

### Content Topics
- Programming languages & frameworks (updates, trends)
- Web development (new tools, techniques)
- AI/ML advancements and applications
- Cloud computing & DevOps
- Cybersecurity trends and discoveries
- Hardware innovations
- Startup/tech company news
- Open-source project updates
- Industry best practices
- Emerging technologies

---

## 🗄️ Database Schema Changes

### New Tables

#### `content_sources` - Whitelisted sources
```sql
CREATE TABLE content_sources (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) UNIQUE,           -- "TechCrunch", "Dev.to"
    url VARCHAR(2048),                  -- Root domain URL
    category VARCHAR(100),              -- "news", "blog", "research"
    language VARCHAR(5) DEFAULT 'en',   -- Primary language
    trustLevel INT DEFAULT 100,         -- 0-100 trust score
    scrapingEnabled BOOLEAN DEFAULT true,
    lastScrapedAt TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Example data:
-- ("TechCrunch", "https://techcrunch.com", "news", "en", 100)
-- ("Dev.to", "https://dev.to", "blog", "en", 95)
```

#### `collected_content` - Raw collected content
```sql
CREATE TABLE collected_content (
    id BIGINT PRIMARY KEY,
    sourceId BIGINT FOREIGN KEY,        -- From which source
    externalUrl VARCHAR(2048) UNIQUE,   -- Original article URL
    title VARCHAR(500),                 -- Original title
    excerpt TEXT,                       -- First 500 chars
    fullContent LONGTEXT,               -- Full article text
    author VARCHAR(255),                -- Original author
    publishedAt TIMESTAMP,              -- Original publish date
    language VARCHAR(5) DEFAULT 'en',   -- Content language
    contentType ENUM('article', 'tutorial', 'news', 'research', 'announcement'),
    imageUrl VARCHAR(2048),             -- Associated image
    isProcessed BOOLEAN DEFAULT false,  -- Has it been paraphrased?
    isDuplicate BOOLEAN DEFAULT false,  -- Duplicate of another?
    duplicateOf BIGINT FOREIGN KEY,     -- Points to original if duplicate
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX(sourceId),
    INDEX(publishedAt),
    INDEX(isProcessed),
    UNIQUE KEY(externalUrl)
);
```

#### `content_aggregations` - Grouped similar content
```sql
CREATE TABLE content_aggregations (
    id BIGINT PRIMARY KEY,
    topic VARCHAR(500),                 -- Unified topic name
    description TEXT,                   -- Why these are grouped
    sourceIds JSON,                     -- ["1", "2", "3"] - which sources contributed
    collectedContentIds JSON,           -- ["10", "15", "20"] - collected_content records
    primarySourceId BIGINT,             -- Which one is primary?
    confidenceScore FLOAT,              -- 0-1 how confident this grouping is
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX(topic),
    INDEX(primarySourceId)
};
```

#### `curated_posts` - Final published posts (replaces generated posts)
```sql
CREATE TABLE curated_posts (
    id BIGINT PRIMARY KEY,
    title VARCHAR(500),
    slug VARCHAR(500) UNIQUE,
    content LONGTEXT,                   -- Paraphrased, elaborated content
    excerpt TEXT,                       -- Summary
    language VARCHAR(5) DEFAULT 'en',   -- Which language version
    baseLanguageId BIGINT FOREIGN KEY,  -- Original language post
    topic VARCHAR(255),                 -- Category/topic

    -- Sourcing
    aggregationId BIGINT FOREIGN KEY,   -- Which aggregation is this based on?
    sourceIds JSON,                     -- ["1", "2", "3"]
    references JSON,                    -- Array of {title, url, author, date}

    -- Content meta
    readingTimeMinutes INT,
    wordCount INT,

    -- SEO & Discovery
    metaDescription VARCHAR(500),
    keywords JSON,                      -- ["tag1", "tag2"]
    featuredImage VARCHAR(2048),

    -- Publishing
    status ENUM('draft', 'review', 'published', 'archived'),
    publishedAt TIMESTAMP,
    reviewedBy BIGINT FOREIGN KEY,      -- Which admin reviewed it?
    reviewNotes TEXT,

    -- Engagement
    viewCount INT DEFAULT 0,
    shareCount INT DEFAULT 0,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX(language),
    INDEX(topic),
    INDEX(status),
    INDEX(publishedAt),
    UNIQUE KEY(slug, language)
};
```

#### `tutorial_collections` - Aggregated tutorials from multiple sources
```sql
CREATE TABLE tutorial_collections (
    id BIGINT PRIMARY KEY,
    title VARCHAR(500),
    slug VARCHAR(500) UNIQUE,
    topic VARCHAR(255),                 -- "Building REST APIs", "React Hooks"
    description TEXT,                   -- Meta description

    -- Sourcing
    sourceIds JSON,                     -- Which tutorials we collected
    collectedContentIds JSON,           -- IDs of original tutorial sources
    references JSON,                    -- [{title, url, author}]

    -- Compilation
    steps JSON,                         -- [{step_num, title, summary, sourceUrl}]
    codeExamples JSON,                  -- Aggregated code snippets
    bestPractices JSON,                 -- Distilled best practices
    commonPitfalls JSON,                -- Warnings

    -- Meta
    skillLevel ENUM('beginner', 'intermediate', 'advanced'),
    language VARCHAR(5) DEFAULT 'en',
    estimatedHours INT,                 -- Time to complete

    -- Publishing
    status ENUM('draft', 'review', 'published'),
    publishedAt TIMESTAMP,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX(topic),
    INDEX(skillLevel),
    UNIQUE KEY(slug)
};
```

#### `source_references` - Clean reference tracking
```sql
CREATE TABLE source_references (
    id BIGINT PRIMARY KEY,
    curatedPostId BIGINT FOREIGN KEY,   -- Which post uses this reference?
    sourceTitle VARCHAR(500),
    sourceUrl VARCHAR(2048) UNIQUE,
    sourceAuthor VARCHAR(255),
    publishedAt TIMESTAMP,
    accessedAt TIMESTAMP,
    domain VARCHAR(255),                -- techcrunch.com, dev.to

    created_at TIMESTAMP,

    INDEX(curatedPostId),
    INDEX(sourceUrl),
    INDEX(domain)
};
```

---

## 🔧 Service Architecture

### 1. **ContentScraperService**
```
Purpose: Automated collection from whitelisted sources

Methods:
- scrapeSource(sourceId, limit)
  * Crawls source website
  * Extracts article title, author, content, publish date
  * Stores in collected_content table
  * Returns count of items collected

- parseContent(html, sourceType)
  * Extracts structured data from HTML
  * Handles different formats (news, blog, research)

- validateContent(content)
  * Checks for minimum quality (word count, structure)
  * Detects obvious spam/ads
  * Returns quality score

Config needed:
- Whitelisted domains list
- CSS selectors per source (for article extraction)
- User-Agent headers for requests
- Rate limiting (polite crawling)
```

### 2. **ContentDeduplicationService**
```
Purpose: Find and group similar content

Methods:
- findDuplicates(collectedContent)
  * Uses TF-IDF + cosine similarity
  * Detects same article from different sources
  * Detects heavily rephrased versions

- createAggregation(duplicateContents)
  * Groups similar articles by topic
  * Calculates which source is "primary"
  * Creates content_aggregation record
  * Returns aggregation ID

- getAggregationScore(content1, content2)
  * Returns 0-1 confidence score
  * 0.95+ = definitely same topic
  * 0.75-0.95 = probably same
  * <0.75 = different
```

### 3. **ParaphrasingService** (Modified from AI generation)
```
Purpose: Paraphrase with source preservation

Methods:
- paraphraseWithSources(aggregation, language)
  * Takes aggregation with multiple sources
  * Extracts key facts from each source
  * Prompts Claude:
    "I have these technology facts from these sources: [sources]
     Please paraphrase and elaborate them as a tech blogger would,
     explaining why readers should care, and preserve all factual accuracy.
     Do NOT add information not in sources. Focus on importance and context."
  * Returns paraphrased content + used sources

- validateFactPreservation(original, paraphrased)
  * Checks if key facts still present
  * Returns missing information warnings

- elaborateForReadability(content)
  * Adds explanatory phrases
  * Breaks down technical concepts
  * Adds "why this matters" sections
```

### 4. **TranslationService**
```
Purpose: Multi-language support

Methods:
- translateContent(curatedPostId, targetLanguages)
  * Creates translated versions
  * Uses Claude or Google Translate API
  * Stores with language tag
  * Maintains source references across languages

- createLanguageVersion(basePostId, language)
  * Generates language-specific slug
  * Adjusts examples for region (if needed)
  * Returns new curated_post record
```

### 5. **ContentAggregatorService**
```
Purpose: Build tutorial collections from scattered sources

Methods:
- aggregateTutorials(topic, maxSources)
  * Finds all tutorial sources on topic
  * Extracts steps from each
  * Deduplicates/consolidates steps
  * Extracts code examples
  * Creates tutorial_collection record

- extractTutorialSteps(content)
  * Parse structured steps from tutorials
  * Returns [{stepNum, title, description, sourceUrl}]

- consolidateCodeExamples(steps)
  * Finds similar code examples
  * Keeps best practices ones
  * Adds language comments
  * Returns consolidated examples
```

### 6. **SourceWhitelistService**
```
Purpose: Manage trusted sources

Methods:
- addSource(name, url, category, trustLevel)
  * Validates domain
  * Sets up scraping config
  * Stores CSS selectors for content extraction

- updateTrustLevel(sourceId, newLevel)
  * 0-100 score
  * Affects whether content auto-publishes

- getScrapeConfig(sourceId)
  * Returns domain, CSS selectors, rate limits

- validateNewSource(sourceId)
  * Test scrape first article
  * Validate content quality
  * Returns pass/fail
```

### 7. **ReferenceTrackingService**
```
Purpose: Attribution and citation

Methods:
- extractReferences(aggregationId)
  * Gets all sources in aggregation
  * Creates clean citations
  * Returns [{title, url, author, domain, date}]

- formatCitations(references, style)
  * Supports: APA, Chicago, Harvard
  * Generates formatted citations

- createSourceFootnotes(content, references)
  * Adds inline [1] [2] citations
  * Creates reference list
  * Returns marked-up content
```

---

## 🔄 Content Pipeline Flow

### Daily Process
```
1. Scrape Sources (06:00 AM)
   └─> ContentScraperService::scrapeAll()
   └─> Stores in collected_content

2. Deduplicate (08:00 AM)
   └─> ContentDeduplicationService::findAllDuplicates()
   └─> Groups into content_aggregations
   └─> Marks duplicates

3. Paraphrase & Elaborate (10:00 AM - 02:00 PM)
   └─> For each aggregation:
       ├─> ParaphrasingService::paraphraseWithSources()
       ├─> Creates curated_post in draft status
       └─> Stores references

4. Translate (02:00 PM - 06:00 PM)
   └─> For each curated_post:
       └─> TranslationService::translateContent(['es', 'fr', 'de', 'zh'])
       └─> Creates language versions

5. Queue for Review (06:00 PM)
   └─> Sends to review queue
   └─> Admin notified
   └─> Status: draft → pending review

6. Manual Review (Admin)
   └─> Check sources are valid
   └─> Verify facts preserved
   └─> Review paraphrasing quality
   └─> Approve/Reject
   └─> Status: review → published/archived

7. Publish (On approval)
   └─> Update SEO metadata
   └─> Generate featured images
   └─> Schedule social posts
   └─> Create affiliate links from references
```

### Tutorial Collection Flow
```
1. Identify Topic (Manual or trending detection)
2. Search collected_content for tutorials on topic
3. ContentAggregatorService::aggregateTutorials(topic)
   └─> Extract steps from each source
   └─> Consolidate code examples
   └─> Compile best practices
4. Create tutorial_collection in draft
5. Admin reviews and publishes
```

---

## 🎯 Workflows & Jobs

### Queue Jobs to Create

#### `ScrapeSingleSourceJob`
```php
Job::dispatch($sourceId);
// Scrapes one source
// Stores results
// Updates lastScrapedAt
```

#### `FindDuplicatesJob`
```php
Job::dispatch($sinceHours = 24);
// Analyzes last 24 hours of collected content
// Creates aggregations
// Marks duplicates
```

#### `ParaphraseAggregationJob`
```php
Job::dispatch($aggregationId, $language = 'en');
// Calls ParaphrasingService
// Creates curated_post
// Extracts references
```

#### `TranslatePostJob`
```php
Job::dispatch($curatedPostId);
// Translates to all configured languages
// Creates language versions
```

#### `ReviewNotificationJob`
```php
Job::dispatch($curatedPostId);
// Sends to admin for review
// Creates review task
```

### Scheduled Commands

#### `php artisan content:scrape-all`
Runs at 6 AM daily - scrapes all whitelisted sources

#### `php artisan content:deduplicate`
Runs at 8 AM daily - finds duplicate content

#### `php artisan content:paraphrase-pending`
Runs at 10 AM, 1 PM, 4 PM - processes queued content

#### `php artisan content:translate-pending`
Runs at 3 PM - translates published posts

#### `php artisan content:prepare-review`
Runs at 6:30 PM - notifies admins of pending reviews

---

## 🛡️ Content Quality Controls

### Automated Checks
1. **Duplicate Detection**
   - TF-IDF similarity score
   - Flag if >75% similar to existing post

2. **Source Validation**
   - Must be whitelisted source
   - Must have valid article structure
   - Must have author and publish date

3. **Fact Preservation Check**
   - Compare original vs paraphrased
   - Flag if major points missing
   - Require confidence score >80%

4. **Language Detection**
   - Verify content matches declared language
   - Flag mixed language content

### Manual Review Workflow
**Admin Actions:**
- ✓ Approve (publish immediately)
- ⚠️ Request Changes (return to draft with notes)
- ✗ Reject (archive, don't publish)
- 🔗 Link Sources (manually add if auto-detection missed)

**Review Checklist:**
- [ ] Sources are reputable
- [ ] Key facts are accurate
- [ ] Paraphrasing adds value (not just rehash)
- [ ] Language quality is good
- [ ] References are complete

---

## 📊 Monitoring & Reporting

### Metrics to Track
```
Daily:
- Sources scraped
- Articles collected
- Duplicates found
- Paraphrase confidence scores
- Translation completeness
- Review queue size

Weekly:
- Most common topics
- Source reliability (by approval rate)
- Reader engagement by source
- Translation quality ratings
- Fact-check issues (if any)

Monthly:
- Content velocity
- Topic trends
- Reader growth by language
- Most valuable sources
- ROI (affiliate clicks from sources)
```

### Admin Dashboard Additions
- Content collection pipeline status
- Pending reviews with confidence scores
- Source health (last scraped, error rate)
- Duplicate detection results
- Translation progress by language

---

## ⚠️ Technical Considerations

### Web Scraping Compliance
- ✓ Respect robots.txt
- ✓ Use polite rate limiting (1 request/sec per domain)
- ✓ Set proper User-Agent header
- ✓ Check terms of service
- ✓ Attribute all sources clearly

### Data Storage
- Store raw content separately from paraphrased
- Keep references linked permanently
- Never delete collected_content (audit trail)
- Archive old paraphrased versions

### Performance
- Use database connection pooling
- Cache deduplication models
- Queue heavy jobs (paraphrase, translate)
- Batch API calls to Claude and translation services
- Index frequently searched columns

---

## 🚀 Implementation Phases

### Phase 1: Infrastructure (Weeks 1-2)
- [ ] Create all new database tables
- [ ] Build ContentScraperService
- [ ] Implement SourceWhitelistService
- [ ] Set up scraping jobs
- [ ] Create Filament admin panels for sources/collected content

### Phase 2: Deduplication & Aggregation (Weeks 2-3)
- [ ] Build ContentDeduplicationService
- [ ] Implement aggregation algorithm
- [ ] Create aggregation management UI
- [ ] Set up deduplication jobs
- [ ] Test on real data

### Phase 3: Content Processing (Weeks 3-4)
- [ ] Modify ParaphrasingService (from generation to paraphrasing)
- [ ] Build TranslationService
- [ ] Implement ReferenceTrackingService
- [ ] Create paraphrase jobs
- [ ] Build translation jobs

### Phase 4: Publishing & Review (Weeks 4-5)
- [ ] Create curated_post model & relationships
- [ ] Build review workflow
- [ ] Create admin review UI
- [ ] Implement approval notifications
- [ ] Connect to existing Filament admin

### Phase 5: Tutorials & Frontend (Week 5-6)
- [ ] Build tutorial collection service
- [ ] Create tutorial aggregation UI
- [ ] Update frontend components for curated content
- [ ] Update post display to show sources
- [ ] Add language switcher

### Phase 6: Testing & Launch (Week 6)
- [ ] End-to-end testing of full pipeline
- [ ] Performance testing with real scraping
- [ ] Security audit of scraper
- [ ] Test with live sources
- [ ] Soft launch

---

## 💾 Data Migration

### From Old System
```
Generated posts → Analysis and manual curation
  ├─> If factually accurate → Convert to curated_post
  ├─> If too generic → Mark as archived
  └─> If useful → Use as reference

Generated tutorials → Tutorial collections
  ├─> Break down into steps
  ├─> Find original sources
  ├─> Build new tutorial_collection
```

---

## 🤝 Team Requirements

### Skills Needed
- **Backend:** Laravel, queue systems, APIs
- **Web Scraping:** HTML parsing, dealing with different site structures
- **NLP:** Content similarity detection (ML)
- **Translations:** Translation APIs, language support
- **Content:** Editorial judgment for reviews, source selection
- **DevOps:** Scaling scraper, managing external API calls

### Roles
- **Scraper Engineer:** Set up and maintain content collection
- **Content Editor:** Review and approve posts
- **Source Manager:** Manage whitelist, evaluate sources
- **QA:** Test deduplication, paraphrasing accuracy

---

## 🎓 Final Notes

This approach positions your platform as:
✓ **Authoritative** - all claims sourced from reputable outlets
✓ **Multi-language** - global reach without language barriers
✓ **Human-curated** - editorial standards, not bot-generated
✓ **Valuable** - elaborated content that teaches, not just informs
✓ **Credible** - transparent sourcing, facts verified
✓ **Discoverable** - proper attribution drives referral traffic

Instead of competing with AI generators, you're **curating and elaborating** on the best of what's already out there, with human judgment and multi-lingual reach.
