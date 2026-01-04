# Content Curation Implementation Roadmap

**Date:** 2026-01-02
**Status:** Ready for Implementation
**Integration Points:** Existing services, jobs, Filament admin, Post model

---

## 🔍 Existing Infrastructure Analysis

### Services Available to Leverage
```
✓ AITutorialGenerationService - Will be adapted (not replaced)
✓ ContentModerationService - For quality checks
✓ SocialShareService - For distribution
✓ NotificationService - For alerts
✓ NewsletterService - For announcements
✓ AffiliateService - Already tracks affiliate links
✓ LemonSqueezyService - Payment processing
```

### Models That Exist
```
Post - Main content model (will be extended)
User - Authors
Category - Content categories
Tag - Content tags
Comment - Reader engagement
```

### Jobs Already Available
```
GenerateTutorialSeriesJob - Will adapt for aggregation
PublishToSocialMediaJob - Can reuse
UpdateEngagementMetricsJob - Already tracks metrics
```

### Filament Admin Panels
```
✓ Blogger Panel - Content creators
✓ Admin Panel - General admin
Will create new resources for:
- ContentSourceResource
- CollectedContentResource
- ContentAggregationResource
- SourceReferenceResource
- TutorialCollectionResource
```

---

## 📊 Implementation Strategy

### Phase 1: Database Layer (Week 1)

**Migrations to create:**
1. ✅ `2026_01_02_000001_create_content_sources_table` - Whitelisted sources
2. ✅ `2026_01_02_000002_create_collected_content_table` - Raw scraped content
3. ✅ `2026_01_02_000003_create_content_aggregations_table` - Grouped content
4. ✅ `2026_01_02_000004_add_sourcing_to_posts_table` - Track sources in posts
5. `2026_01_02_000005_create_source_references_table` - Citations
6. `2026_01_02_000006_create_tutorial_collections_table` - Aggregated tutorials

**Models to create:**
```
app/Models/ContentSource.php
app/Models/CollectedContent.php
app/Models/ContentAggregation.php
app/Models/SourceReference.php
app/Models/TutorialCollection.php
```

**Post Model Changes:**
- Update relationships in Post model to reference ContentAggregation
- Add methods for source/reference management
- Add scopes for filtering curated vs original content

---

### Phase 2: Content Collection Services (Week 2)

**New Services:**
```
app/Services/ContentScraperService.php
  - scrapeSource(sourceId, limit)
  - parseContent(html, sourceType)
  - validateContent(content)

app/Services/SourceWhitelistService.php
  - addSource(name, url, category, trustLevel)
  - validateNewSource(sourceId)
  - getScrapeConfig(sourceId)

app/Services/ContentDeduplicationService.php
  - findDuplicates(collectedContent)
  - createAggregation(duplicateContents)
  - getAggregationScore(content1, content2)
```

**New Jobs:**
```
app/Jobs/ScrapeSingleSourceJob.php
app/Jobs/FindDuplicatesJob.php
app/Jobs/ScrapeAllSourcesJob.php
```

**Scheduled Commands:**
```
app/Console/Commands/ScrapeSourcesCommand.php (6 AM daily)
app/Console/Commands/FindDuplicatesCommand.php (8 AM daily)
```

---

### Phase 3: Content Processing (Week 3-4)

**Adapt Existing Service:**
```
app/Services/AITutorialGenerationService.php
  - Modify: Add method paraphraseWithSources(aggregation, language)
  - Modify: Add validateFactPreservation(original, paraphrased)
  - Keep: Existing methods for compatibility
```

**New Services:**
```
app/Services/ParaphrasingService.php
  - paraphraseWithSources(aggregation, language)
  - elaborateForReadability(content)
  - validateFactPreservation(original, paraphrased)

app/Services/TranslationService.php
  - translateContent(curatedPostId, targetLanguages)
  - createLanguageVersion(basePostId, language)

app/Services/ReferenceTrackingService.php
  - extractReferences(aggregationId)
  - formatCitations(references, style)
  - createSourceFootnotes(content, references)

app/Services/ContentAggregatorService.php
  - aggregateTutorials(topic, maxSources)
  - extractTutorialSteps(content)
  - consolidateCodeExamples(steps)
```

**New Jobs:**
```
app/Jobs/ParaphraseAggregationJob.php
app/Jobs/TranslatePostJob.php
app/Jobs/AggregatetutorialsJob.php
app/Jobs/ExtractReferencesJob.php
```

**Scheduled Commands:**
```
app/Console/Commands/ParaphrasePendingCommand.php (10 AM, 1 PM, 4 PM)
app/Console/Commands/TranslatePendingCommand.php (3 PM)
app/Console/Commands/PrepareReviewCommand.php (6:30 PM)
```

---

### Phase 4: Admin & Review (Week 4-5)

**Filament Resources:**
```
app/Filament/Admin/Resources/ContentSourceResource.php
  - List sources with trust levels
  - Edit scraping configuration
  - Monitor last scrape time

app/Filament/Admin/Resources/CollectedContentResource.php
  - Browse collected articles
  - Mark as processed/duplicates
  - Bulk actions

app/Filament/Admin/Resources/ContentAggregationResource.php
  - View grouped content
  - Adjust primary source
  - Confidence scores

app/Filament/Admin/Resources/CuratedPostsResource.php
  - Different from MyPostResource (for curation vs authoring)
  - Review workflow (draft → pending → published)
  - Show sources and references
  - Fact verification tracking

app/Filament/Admin/Resources/SourceReferenceResource.php
  - View citations
  - Format management
```

**Review Workflow:**
```
PostReviewController - Handle approve/reject
- Extend existing moderation system
- Add source validation checks
- Add fact-check notes field
```

**Livewire Components (update existing):**
```
AdminTutorialGenerator - Modify for aggregation
TutorialBrowser - Add source display
```

---

### Phase 5: Frontend Display (Week 5)

**Update components to show sources:**
```
resources/views/posts/show.blade.php
  - Add "Sources" section at bottom
  - Show citation formatting
  - Link to original articles

resources/views/components/post-card.blade.php
  - Add "Curated from X sources" badge

resources/views/components/source-list.blade.php
  - New component for displaying references
```

**New Page:**
```
resources/views/pages/curated-topics.blade.php
  - Show trending topics
  - Filter by source type
  - Filter by language
  - Collections of related curated posts
```

---

## 🔗 Default Content Sources (Whitelist)

Initial sources to set up:
```php
[
    [
        'name' => 'TechCrunch',
        'url' => 'https://techcrunch.com',
        'category' => 'news',
        'trust_level' => 100,
        'css_selectors' => [
            'title' => 'h1[data-post-id]',
            'content' => 'div[class*="post-content"]',
            'author' => 'span[class*="byline"]',
        ]
    ],
    [
        'name' => 'Dev.to',
        'url' => 'https://dev.to',
        'category' => 'blog',
        'trust_level' => 95,
    ],
    // ... more sources
]
```

---

## 📋 Database Migration Schedule

| Order | Migration | Purpose | Status |
|-------|-----------|---------|--------|
| 1 | `...001_create_content_sources_table` | Source management | ✅ Ready |
| 2 | `...002_create_collected_content_table` | Raw content storage | ✅ Ready |
| 3 | `...003_create_content_aggregations_table` | Group similar content | ✅ Ready |
| 4 | `...004_add_sourcing_to_posts_table` | Extend posts for curation | ✅ Ready |
| 5 | `...005_create_source_references_table` | Citation tracking | Pending |
| 6 | `...006_create_tutorial_collections_table` | Tutorial aggregation | Pending |

---

## 🎯 Key Design Decisions

### Why Extend Posts Table vs Create Separate Table
```
Benefits:
✓ All existing Post features work with curated posts
✓ SEO, analytics, comments, tags, categories all integrated
✓ Simpler queries and relationships
✓ Easier migration path
✓ Works with existing Filament resources
```

### How Sources are Tracked
```
Each Post has:
- is_curated: boolean (true = from sources)
- content_aggregation_id: links to aggregation
- source_ids: JSON array of contributing sources
- references: JSON array of citations
- paraphrase_confidence_score: quality metric

This allows filtering:
- Original vs curated posts
- Posts by source
- Posts by confidence level
```

### Language Handling
```
Single Post record per language:
- base_post_id: Links to original language version
- base_language: Which language is "original"

Advantages:
✓ Separate URLs and slugs per language
✓ Independent SEO for each version
✓ Can be published on different schedules
✓ Works with existing slug system
```

---

## 🚀 Execution Order (Optimal)

```
Week 1:
☐ Create remaining migrations (5, 6)
☐ Generate models from migrations
☐ Update Post model relationships
☐ Run migrations in test environment

Week 2:
☐ Create ContentScraperService
☐ Create SourceWhitelistService
☐ Create all scraper jobs
☐ Set up scheduled commands
☐ Test with real sources (1-2)

Week 3:
☐ Create ContentDeduplicationService
☐ Create ParaphrasingService
☐ Create TranslationService
☐ Create ReferenceTrackingService
☐ Test full pipeline on sample data

Week 4:
☐ Create Filament admin resources
☐ Build review workflow
☐ Create new jobs
☐ Test admin interface

Week 5:
☐ Update frontend components
☐ Add source display to post views
☐ Create curated topics page
☐ User acceptance testing

Week 6:
☐ Performance optimization
☐ Security audit
☐ Documentation
☐ Soft launch with limited sources
```

---

## ⚠️ Important Notes

### Keep These Intact
```
✓ Posts table - just extending it
✓ User authentication - no changes
✓ Existing Services - reusing where possible
✓ Filament structure - following conventions
✓ Job scheduling - using existing system
```

### Replace These
```
→ AITutorialGenerationService (will add, not replace)
→ Content generation logic (add paraphrasing mode)
```

### Not Touch (For Now)
```
• Monetization system - fully compatible
• Social media publishing - will enhance it
• Reader tracking - will expand it
• Analytics - will add to it
```

---

## 📞 Data Flow Summary

```
1. COLLECTION
   ContentScraperService → CollectedContent table
   (8 different sources, 100+ articles/day)

2. DEDUPLICATION
   ContentDeduplicationService → ContentAggregation table
   (Groups similar articles, 70+ topics/day)

3. PARAPHRASING
   ParaphrasingService (uses Claude) → Post table (is_curated=true)
   (10-15 posts/day)

4. TRANSLATION
   TranslationService → Creates language versions
   (Multiply posts by language count)

5. REVIEW
   Admin review workflow → Approval/Rejection
   (Manual quality gate)

6. PUBLISHING
   Post publication → Social media, newsletters
   (Existing infrastructure)

7. MONETIZATION
   Affiliate links from references
   Direct tips, subscriptions
   (Existing systems)
```

---

## ✅ Success Criteria

- [ ] All migrations run successfully
- [ ] Can scrape and store content from 3+ sources
- [ ] Deduplication identifies 70%+ of duplicates
- [ ] Paraphrasing preserves 90%+ of facts
- [ ] Admin can review and approve posts
- [ ] Published posts show sources and citations
- [ ] Multi-language versions created automatically
- [ ] Affiliate links tracked from sources
- [ ] Reader feedback positive on content quality
- [ ] No fact-checking complaints in first month

---

**Next Step:** Finalize and run remaining migrations, then begin Phase 2 service development.
