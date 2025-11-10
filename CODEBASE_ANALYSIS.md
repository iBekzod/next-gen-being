# Codebase Analysis - Missing Models, Migrations, and Dependencies

## Analysis Date: 2025-11-10

---

## 1. MISSING MODELS

### 1.1 PostInteraction Model (CRITICAL - MISSING)

**Status:** Missing - Referenced but does not exist

**Location Referenced:**
- app/Services/RecommendationService.php (lines 7, 18, 23, 234)

**Current Usage in RecommendationService:**
The model is used to query:
- Liked posts by user (type = 'like')
- Viewed posts by user (type = 'view')
- Recent engagement on posts (created_at >= 7 days)

**What it should contain:**

Fields:
- id (primary key)
- user_id (foreign key to users)
- post_id (foreign key to posts)
- type (string: 'like', 'view', 'comment', 'share')
- metadata (json, optional)
- created_at
- updated_at

Relationships:
- belongsTo(User::class)
- belongsTo(Post::class)

Scopes:
- scopeOfType($query, $type)
- scopeLikes($query)
- scopeViews($query)

---

## 2. MISSING MIGRATIONS

### 2.1 post_interactions Table (CRITICAL - MISSING)

**Status:** No migration exists

**Required Table Columns:**
- id (bigint, primary)
- user_id (bigint, unsigned, foreign key to users)
- post_id (bigint, unsigned, foreign key to posts)
- type (string) - values: 'like', 'view', 'comment', 'share'
- metadata (json, nullable)
- created_at (timestamp)
- updated_at (timestamp)

**Indexes Needed:**
- (user_id, type) - for filtering user interactions by type
- (post_id, type) - for filtering post interactions by type
- created_at - for time-based queries
- unique constraint: (user_id, post_id, type) - user can only like a post once

---

### 2.2 Achievements/User Achievements Tables (SCHEMA MISMATCH)

**Status:** Partial - Migration exists but doesn't match the Model

**Active Migration:** 2025_11_08_000002_create_badges_and_reputation_tables.php
Creates these tables:
- achievements (id, name, slug, description, icon, color, points, order, is_active, requirements, timestamps)
- user_achievements (id, user_id, achievement_id, achieved_at, metadata, timestamps)
- badges
- user_badges
- user_reputation

**User Achievement Model Structure:**
File: app/Models/UserAchievement.php
Expects these fields:
- user_id
- achievement_code (NOT achievement_id)
- description
- metadata
- achieved_at

**THE PROBLEM:**
The migration creates user_achievements with:
- achievement_id (FK to achievements table)

But the UserAchievement model expects:
- achievement_code (string field, not a foreign key)

This is a structural mismatch that will cause runtime errors.

---

## 3. EXISTING RELATED MODELS

All found and working:
- User (app/Models/User.php)
- Post (app/Models/Post.php)
- Achievement (app/Models/Achievement.php)
- UserAchievement (app/Models/UserAchievement.php) - SCHEMA MISMATCH
- Badge (app/Models/Badge.php)
- UserReputation (app/Models/UserReputation.php)
- Category (app/Models/Category.php)
- Tag (app/Models/Tag.php)
- UserInteraction (app/Models/UserInteraction.php) - polymorphic, alternative to PostInteraction

---

## 4. RECOMMENDATION SERVICE METHODS

**File:** app/Services/RecommendationService.php

1. getRecommendationsForUser(User $user, $limit = 5)
   - Purpose: Get personalized post recommendations
   - Uses: PostInteraction (MISSING), Post, Category, Tag
   - Logic: Scores posts based on favorite categories, tags, followed authors, engagement

2. getSimilarPosts(Post $post, $limit = 5)
   - Purpose: Find posts similar to a given post
   - Uses: Post, Category, Tag
   - Logic: Matches category, tags, author, engagement

3. getTrendingPosts($limit = 5)
   - Purpose: Get trending posts
   - Uses: Post
   - Logic: Orders by views_count

4. getFollowedAuthorPosts(User $user, $limit = 5)
   - Purpose: Get posts from followed authors
   - Uses: User.following() relationship, Post
   - Logic: Queries followed authors and their posts

5. getEditorsPicks($limit = 5)
   - Purpose: Get curated high-engagement posts
   - Uses: Post
   - Logic: Uses raw SQL for engagement_rate calculation

6. calculateEngagementScore(Post $post)
   - Purpose: Calculate engagement as percentage
   - Uses: Post.views_count, likes_count, comments count
   - Formula: (likes + comments) / views * 100

7. isTrending(Post $post)
   - Purpose: Check if post is trending
   - Uses: PostInteraction (MISSING)
   - Logic: Post has > 10 interactions in last 7 days

---

## 5. LEARNING PROGRESS CARD COMPONENT

**File:** resources/views/components/learning-progress-card.blade.php

**What It Does:**
- Shows user learning stats: parts completed, series completed, hours spent, points
- Displays achievements earned: auth()->user()->achievements()->latest('earned_at')->take(6)->get()
- Uses TutorialProgressService to get stats

**Dependencies:**
- User.achievements() relationship - EXISTS
- Achievement model - EXISTS
- user_achievements table with earned_at field - EXISTS (in migration)
- x-achievement-badge component - needs verification

---

## 6. DATABASE RELATIONSHIPS

User Model (app/Models/User.php):
- achievements() → belongsToMany(Achievement::class, 'user_achievements')
    - withPivot('earned_at')
    - withTimestamps()

Achievement Model (app/Models/Achievement.php):
- users() → belongsToMany(User::class, 'user_achievements')
    - withPivot('earned_at')
    - withTimestamps()

Current Status: The relationship exists and should work IF the user_achievements table has the correct schema

---

## 7. CRITICAL ACTION ITEMS

ACTION 1: Create PostInteraction Model
- File: app/Models/PostInteraction.php
- Implement: id, user_id, post_id, type, metadata fields
- Add: belongsTo relationships for User and Post
- Add: scopes for type filtering

ACTION 2: Create post_interactions Migration
- File: database/migrations/2025_11_XX_XXXXXX_create_post_interactions_table.php
- Create table with all required fields and indexes
- Add foreign key constraints

ACTION 3: Fix UserAchievement Schema Mismatch
EITHER:
  Option A: Update UserAchievement model to use achievement_id FK
  Option B: Update migration to include achievement_code field
  Option C: Create new migration to alter user_achievements table

ACTION 4: Verify Post Model
- Check for published() scope
- Check for views_count, likes_count fields
- Check for category_id, author_id fields

---

## 8. IMPORTANT NOTES

- UserInteraction model (polymorphic) exists as an alternative approach
- RecommendationService specifically expects PostInteraction model
- Achievement-related code has working migration but model/migration mismatch
- Learning-progress-card component correctly uses relationships (line 62)

