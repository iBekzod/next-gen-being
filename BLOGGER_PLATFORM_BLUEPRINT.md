# Blogger Platform Blueprint

## Overview
Transform the single-author blog into a **multi-blogger platform** with AI-assisted content creation and monetization.

---

## ✅ Already Implemented

### 1. User & Role System
- ✅ User model with profiles (avatar, bio, website, social links)
- ✅ Role-based permissions (Admin, Content Manager, Lead, **Blogger**)
- ✅ User authentication system

### 2. Follower System
- ✅ `user_follows` table (migration: `2025_10_06_054901`)
- ✅ Follow/unfollow methods in User model:
  - `followers()` - Get user's followers
  - `following()` - Get users this user follows
  - `isFollowing(User $user)` - Check if following
  - `follow(User $user)` - Follow a user
  - `unfollow(User $user)` - Unfollow a user

### 3. Content System
- ✅ Posts with series support (tutorials)
- ✅ Premium content tiers
- ✅ Content moderation system
- ✅ AI post generation via Groq API
- ✅ Image generation service
- ✅ Categories and tags

### 4. Monetization Foundation
- ✅ LemonSqueezy subscription integration
- ✅ Premium content access control
- ✅ **NEW**: `blogger_earnings` table for tracking revenue

---

## 🚧 To Be Implemented

### Phase 1: AI-Powered Content Creation for Bloggers

#### 1.1 Prompt-to-Post Generation
**File**: `app/Console/Commands/GeneratePostFromPrompt.php`

```bash
php artisan blogger:generate-from-prompt \
  --prompt="Write about Laravel 11 new features" \
  --author=5 \
  --with-image \
  --draft
```

**Features**:
- Accept natural language prompt
- Generate full blog post with AI
- Auto-generate featured image
- Support for series generation
- Draft/published workflow
- Premium/free designation

**Implementation**:
```php
protected $signature = 'blogger:generate-from-prompt
                        {--prompt= : Natural language prompt describing the post}
                        {--author= : Blogger user ID}
                        {--with-image : Generate AI image}
                        {--draft : Save as draft}
                        {--premium : Mark as premium}
                        {--series= : Generate as series (number of parts)}';
```

#### 1.2 AI Writing Assistant Service
**File**: `app/Services/AIWritingAssistant.php`

**Features**:
- **Expand**: Make a paragraph longer with more details
- **Shorten**: Condense text while keeping key points
- **Improve**: Enhance tone, grammar, clarity
- **Rewrite**: Alternative phrasing
- **Generate Ideas**: Suggest topics based on niche
- **SEO Optimize**: Add keywords, improve meta descriptions

**API Endpoints**:
```php
POST /api/ai/expand       // Expand text
POST /api/ai/shorten      // Shorten text
POST /api/ai/improve      // Improve quality
POST /api/ai/rewrite      // Rewrite text
POST /api/ai/ideas        // Generate content ideas
POST /api/ai/seo-optimize // SEO improvements
```

#### 1.3 AI Image Generation Integration
**Service**: `app/Services/ImageGenerationService.php` (already exists, needs expansion)

**Providers to Support**:
1. **Unsplash** (current, free) ✅
2. **DALL-E 3** (OpenAI, paid, high quality)
3. **Stable Diffusion** (Stability AI, paid, custom)
4. **Midjourney** (via API, premium)

**Implementation**:
```php
// Add to .env
OPENAI_API_KEY=sk-...
STABILITY_API_KEY=sk-...

// Usage
$imageService->generateFromPrompt(
    prompt: "Modern tech workspace with laptop and coffee",
    provider: 'dalle',  // dalle, stable-diffusion, unsplash
    style: 'photorealistic'
);
```

---

### Phase 2: Blogger Dashboard & Management

#### 2.1 Separate Blogger Panel (Filament)
**File**: `app/Providers/Filament/BloggerPanelProvider.php`

**Access**: `/blogger` (separate from `/admin`)

**Features**:
- Own posts management
- Draft/publish workflow
- Analytics dashboard
- Earnings tracker
- Follower insights
- AI content tools

**Create Panel**:
```bash
php artisan make:filament-panel blogger
```

**Configuration**:
```php
->id('blogger')
->path('blogger')
->login()
->colors(['primary' => Color::Blue])
->authMiddleware([Authenticate::class])
->authGuard('web')
->tenantMiddleware(['blogger'], isPersistent: false)
```

#### 2.2 Blogger Dashboard Widgets
**Widgets to Create**:

1. **Stats Overview Widget**
   - Total Posts (Free/Premium)
   - Total Followers
   - Total Views (last 30 days)
   - Total Earnings (pending/paid)

2. **Earnings Chart Widget**
   - Monthly earnings trend
   - Breakdown by type (followers, premium, engagement)
   - Pending vs paid

3. **Follower Growth Widget**
   - Follower count over time
   - Growth rate
   - Next milestone tracker

4. **Top Posts Widget**
   - Most viewed posts
   - Most liked posts
   - Best performing premium content

5. **Quick Actions Widget**
   - Generate new post with AI
   - View pending drafts
   - Check earnings status

#### 2.3 Blogger Resources (Filament)

**PostResource** (Blogger-specific):
- Can only see/edit own posts
- AI writing assistant integration
- Image generation button
- Premium pricing controls

**FollowerResource**:
- View followers
- See follower activity
- Export follower list

**EarningResource**:
- View earnings history
- See pending payouts
- Request payout
- Download reports

---

### Phase 3: Monetization System

#### 3.1 Follower Milestone Rewards
**File**: `app/Services/BloggerMonetizationService.php`

**Milestones**:
```php
const FOLLOWER_MILESTONES = [
    10 => 5.00,      // $5 for first 10 followers
    50 => 15.00,     // $15 for 50 followers
    100 => 50.00,    // $50 for 100 followers
    250 => 100.00,   // $100 for 250 followers
    500 => 250.00,   // $250 for 500 followers
    1000 => 500.00,  // $500 for 1,000 followers
    2500 => 1000.00, // $1,000 for 2,500 followers
    5000 => 2500.00, // $2,500 for 5,000 followers
    10000 => 5000.00 // $5,000 for 10,000 followers
];
```

**Auto-Detection**:
```php
// Event Listener on UserFollow
Event::listen(UserFollowed::class, function ($event) {
    $blogger = $event->followedUser;
    $followerCount = $blogger->followers()->count();

    BloggerMonetizationService::checkMilestones($blogger, $followerCount);
});
```

#### 3.2 Premium Content Revenue Sharing
**Model**: Already exists (Post premium_tier field)

**Revenue Share**:
- 70% to blogger
- 30% to platform

**Tracking**:
```php
// When user purchases premium content access
BloggerEarning::createPremiumContentEarning(
    user: $blogger,
    post: $post,
    amount: $purchaseAmount * 0.70 // 70% to blogger
);
```

#### 3.3 Engagement Bonuses
**Metrics**:
- Views milestone (10k, 50k, 100k views)
- Likes milestone (100, 500, 1000 likes)
- Comments engagement (50, 200, 500 comments)

**Example**:
```php
// Weekly engagement bonus calculation
$views = $blogger->posts()->sum('views');
if ($views >= 10000 && !$blogger->hasReceivedBonus('10k_views')) {
    BloggerEarning::createEngagementBonus(
        user: $blogger,
        amount: 25.00,
        metadata: ['type' => '10k_views', 'actual_views' => $views]
    );
}
```

#### 3.4 Payout System
**Admin Command**:
```bash
php artisan blogger:process-payouts --minimum=50
```

**Integration**:
- Stripe Connect (for direct payouts)
- PayPal Payouts API
- Manual bank transfer option

**Workflow**:
1. Blogger reaches minimum threshold ($50)
2. Blogger requests payout via dashboard
3. Admin reviews and approves
4. Automatic payout via Stripe/PayPal
5. Earning marked as "paid" with transaction reference

---

### Phase 4: Frontend Features

#### 4.1 Blogger Public Profiles
**Route**: `/blogger/{username}`

**Features**:
- Bio, avatar, social links
- All published posts
- Follower count
- Follow/unfollow button
- Statistics (posts, views, followers)

**Blade Template**: `resources/views/bloggers/profile.blade.php`

#### 4.2 Follow/Unfollow UI
**Livewire Component**: `app/Livewire/FollowButton.php`

```php
<livewire:follow-button :user="$blogger" />
```

**Features**:
- Real-time follow/unfollow
- Follower count updates
- Login prompt for guests
- Notification to blogger on new follower

#### 4.3 Personalized Feed
**Route**: `/feed` or `/following`

**Features**:
- Show posts only from followed bloggers
- Chronological or algorithm-based sorting
- Filter by blogger
- Save for later functionality

**Implementation**:
```php
$followedBloggers = auth()->user()->following()->pluck('id');

$posts = Post::published()
    ->whereIn('author_id', $followedBloggers)
    ->latest('published_at')
    ->paginate(20);
```

#### 4.4 Blogger Discovery Page
**Route**: `/bloggers` or `/discover`

**Features**:
- List all active bloggers
- Sort by: Most followers, Most posts, Newest
- Search by name/expertise
- Filter by category/niche
- Featured bloggers section

---

### Phase 5: Notifications & Engagement

#### 5.1 Blogger Notifications
**Events**:
- New follower
- Milestone reached (followers, views, earnings)
- Post published successfully
- Earnings paid out
- Comment on post
- Post featured by admin

#### 5.2 Follower Notifications
**Events**:
- Blogger published new post
- Weekly digest of followed bloggers
- Blogger reached milestone (celebrate together!)

#### 5.3 Email Campaigns
**Automated Emails**:
- Welcome email for new bloggers
- Monthly earnings report
- Payout confirmation
- Follower milestone celebration
- Content performance summary

---

## Database Schema Summary

### New Tables Created
1. ✅ `blogger_earnings` - Track all blogger revenue

### Existing Tables Used
1. ✅ `users` - Bloggers are users with "blogger" role
2. ✅ `user_follows` - Follower relationships
3. ✅ `posts` - Content with premium tiers
4. ✅ `roles` - Permission system
5. ✅ `user_roles` - User-role assignments

---

## API Endpoints Needed

### Blogger API
```
POST   /api/blogger/generate-post         # Generate post from prompt
POST   /api/blogger/generate-image        # Generate AI image
GET    /api/blogger/stats                 # Dashboard stats
GET    /api/blogger/earnings              # Earnings history
POST   /api/blogger/request-payout        # Request payout

# AI Writing Assistant
POST   /api/ai/expand                     # Expand text
POST   /api/ai/shorten                    # Shorten text
POST   /api/ai/improve                    # Improve text
POST   /api/ai/rewrite                    # Rewrite text
POST   /api/ai/ideas                      # Generate ideas
POST   /api/ai/seo-optimize               # SEO optimization

# Following
POST   /api/users/{id}/follow             # Follow user
DELETE /api/users/{id}/follow             # Unfollow user
GET    /api/users/{id}/followers          # Get followers
GET    /api/users/{id}/following          # Get following

# Feed
GET    /api/feed                          # Personalized feed
GET    /api/feed/following                # Posts from followed bloggers
```

---

## Configuration Files

### .env Additions
```bash
# AI Providers
GROQ_API_KEY=gsk-...                      # Already configured
OPENAI_API_KEY=sk-...                     # For DALL-E 3
STABILITY_API_KEY=sk-...                  # For Stable Diffusion

# Monetization
BLOGGER_MINIMUM_PAYOUT=50.00              # Minimum payout threshold
BLOGGER_REVENUE_SHARE=0.70                # 70% to blogger
PLATFORM_REVENUE_SHARE=0.30               # 30% to platform

# Stripe Connect (for payouts)
STRIPE_CONNECT_CLIENT_ID=ca_...
STRIPE_CONNECT_SECRET=sk_...
```

### config/blogger.php (New)
```php
return [
    'enabled' => env('BLOGGER_PLATFORM_ENABLED', true),
    'minimum_payout' => env('BLOGGER_MINIMUM_PAYOUT', 50.00),
    'revenue_share' => env('BLOGGER_REVENUE_SHARE', 0.70),

    'follower_milestones' => [
        10 => 5.00,
        50 => 15.00,
        100 => 50.00,
        // ... etc
    ],

    'engagement_bonuses' => [
        'views' => [
            10000 => 25.00,
            50000 => 100.00,
            100000 => 250.00,
        ],
        'likes' => [
            100 => 10.00,
            500 => 50.00,
            1000 => 100.00,
        ],
    ],
];
```

---

## Implementation Priority

### Phase 1 (Week 1-2): Foundation
1. ✅ Blogger earnings table & model
2. ⏳ AI prompt-to-post command
3. ⏳ AI writing assistant service
4. ⏳ Enhanced image generation

### Phase 2 (Week 3-4): Dashboard
1. Create blogger Filament panel
2. Build dashboard widgets
3. Create blogger-specific resources
4. Add analytics tracking

### Phase 3 (Week 5-6): Monetization
1. Implement follower milestone system
2. Add premium revenue sharing
3. Create engagement bonus calculator
4. Build payout system

### Phase 4 (Week 7-8): Frontend
1. Blogger public profiles
2. Follow/unfollow UI
3. Personalized feed
4. Blogger discovery page

### Phase 5 (Week 9-10): Polish
1. Notifications system
2. Email campaigns
3. Admin moderation tools
4. Performance optimization

---

## Success Metrics

### Platform Success
- Number of active bloggers
- Total followers in platform
- Posts published per week
- Premium content conversion rate
- Platform revenue

### Blogger Success
- Average earnings per blogger
- Follower growth rate
- Post engagement (views, likes, comments)
- Premium content sales
- Payout frequency

---

## Next Steps

1. **Run migration**: `docker exec ngb-app php artisan migrate`
2. **Create AI prompt-to-post command** (in progress)
3. **Build AI writing assistant service**
4. **Create blogger Filament panel**
5. **Test with sample bloggers**

---

## Questions to Clarify

1. **Image Generation**:
   - Which AI image provider? (DALL-E 3 recommended but costs ~$0.04/image)
   - Or stick with free Unsplash?
   - Budget for image generation?

2. **Monetization**:
   - What milestone amounts work for your budget?
   - Stripe Connect or manual payouts initially?
   - Minimum payout threshold? ($50 recommended)

3. **Platform**:
   - Open registration for bloggers or invite-only?
   - Admin approval required for new bloggers?
   - Content moderation before publishing?

4. **Revenue Model**:
   - 70/30 split reasonable? (industry standard)
   - Platform subscription for bloggers or free?
   - Premium content pricing strategy?

---

**Total Development Time Estimate**: 8-10 weeks for full implementation
**MVP (Minimum Viable Product)**: 3-4 weeks (Phases 1-2)
