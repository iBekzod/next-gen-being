# Content Collaboration System Guide

## Overview

The Content Collaboration System enables team-based content creation and editing on NextGenBeing. Multiple users can collaborate on a single post with defined roles, permissions, and editorial workflows.

## Key Features

### 1. **Collaborator Roles & Permissions**

Four distinct roles control what collaborators can do:

| Role | Permissions | Use Case |
|------|-------------|----------|
| **Owner** | View, edit, review, invite, manage collaborators | Post author (automatic) |
| **Editor** | View, edit, review | Co-authors, team members |
| **Reviewer** | View, review, comment | Editors, content reviewers |
| **Viewer** | View only | Stakeholders, interested parties |

### 2. **Collaboration Invitations**

- Invite users by email address
- Invitations expire after 7 days (configurable)
- Users can accept/decline invitations
- Track pending invitations with reminder system
- Cancel invitations anytime

### 3. **Editorial Comments & Discussion**

- Add comments to specific post sections
- Support for threaded replies
- Status tracking: Open, Needs Discussion, Resolved
- Comment resolution workflows
- Collaborative feedback collection

### 4. **Version Control & History**

Track all changes to a post:

- Auto-save versions
- Manual save points with summaries
- Publication history
- Version restoration (restore to any previous version)
- Editor attribution for each version
- Change type tracking (auto-save, manual, published, scheduled)

### 5. **Collaboration Activity Log**

Complete audit trail of:

- Who joined/left collaboration
- Role changes
- Content edits
- Comments added/resolved
- Versions created
- Publication events

### 6. **Collaboration Statistics**

Real-time metrics:

- Total collaborators breakdown by role
- Total and open comments count
- Total versions created
- Last edited timestamp and editor
- Collaboration status overview

## Database Schema

### Core Tables

#### `post_collaborators`
Tracks active collaborators on a post.

```sql
- id (primary key)
- post_id (foreign key)
- user_id (foreign key)
- role (enum: owner, editor, reviewer, viewer)
- permissions (json) - custom role overrides
- joined_at (timestamp)
- left_at (timestamp, nullable) - when they stopped collaborating
```

#### `collaboration_invitations`
Pending and accepted collaboration invitations.

```sql
- id (primary key)
- post_id (foreign key)
- inviter_id (foreign key)
- email (string)
- user_id (foreign key, nullable)
- role (enum: editor, reviewer, viewer)
- status (enum: pending, accepted, declined, cancelled)
- token (string, unique) - secure invitation link
- expires_at (timestamp)
- accepted_at (timestamp, nullable)
- declined_at (timestamp, nullable)
```

#### `collaboration_comments`
Editorial comments and discussion threads.

```sql
- id (primary key)
- post_id (foreign key)
- user_id (foreign key)
- content (text)
- section (string, nullable) - which section comment is on
- line_number (int, nullable) - specific line reference
- status (enum: open, needs_discussion, resolved)
- resolved_at (timestamp, nullable)
- resolved_by (foreign key, nullable)
- parent_comment_id (foreign key, nullable) - for nested replies
```

#### `post_versions`
Complete version history of posts.

```sql
- id (primary key)
- post_id (foreign key)
- edited_by (foreign key)
- title (text)
- content (longtext)
- content_json (longtext, nullable) - editor state (if using block editor)
- change_summary (string, nullable)
- change_type (enum: auto_save, manual_save, published, scheduled)
- changes_metadata (json, nullable) - detailed change tracking
- created_at (timestamp)
```

#### `collaboration_activities`
Audit trail of all collaboration actions.

```sql
- id (primary key)
- post_id (foreign key)
- user_id (foreign key)
- action (enum: invited, joined, left, role_changed, content_edited, comment_added, comment_resolved, version_created, published)
- description (text, nullable)
- metadata (json, nullable) - additional context
- created_at (timestamp)
```

## Usage Examples

### For Authors/Owners

#### 1. Invite a Collaborator

```blade
@livewire('collaboration-invite', ['post' => $post])
```

In Livewire component:
```php
use App\Services\CollaborationService;

$collaborationService = app(CollaborationService::class);
$invitation = $collaborationService->inviteCollaborator(
    post: $post,
    inviter: $owner,
    email: 'collaborator@example.com',
    role: 'editor',
    expiresInDays: 7
);
```

#### 2. Manage Collaborators

```blade
@livewire('collaboration-manager', ['post' => $post])
```

Features:
- View active collaborators
- Change roles
- Remove collaborators
- Cancel pending invitations

#### 3. View Collaboration History

```blade
@livewire('version-history', ['post' => $post])
```

Features:
- See all versions
- View who edited when
- Restore previous versions
- Compare changes

### For Collaborators

#### 1. Accept Invitation

Email link: `/collaboration/invitation/accept?token={token}`

```php
// In CollaborationController
public function acceptInvitation(Request $request)
{
    $token = $request->query('token');
    $invitation = CollaborationInvitation::where('token', $token)->first();

    $collaborationService->acceptInvitation($invitation, Auth::user());
}
```

#### 2. Add Editorial Comments

```blade
@livewire('collaboration-comments', ['post' => $post])
```

Features:
- Add comments to post sections
- Reply to comments (threaded)
- Resolve when changes are made
- Track discussion status

#### 3. View and Restore Versions

```blade
@livewire('version-history', ['post' => $post])
```

Features:
- Browse version history
- View what changed
- Restore previous versions if you have edit permission

## API Routes

```php
// Accept invitation
GET /collaboration/invitation/accept?token={token}

// Decline invitation
POST /collaboration/invitation/{invitation}/decline

// View collaboration notifications
GET /collaborations

// Collaboration management
GET /posts/{post}/collaboration
GET /posts/{post}/collaboration/history
GET /posts/{post}/collaboration/export
```

## Service Layer Methods

### `CollaborationService`

```php
// Invitations
inviteCollaborator(Post $post, User $inviter, string $email, string $role)
acceptInvitation(CollaborationInvitation $invitation, User $user): bool
declineInvitation(CollaborationInvitation $invitation): bool

// Collaborator Management
removeCollaborator(Post $post, User $collaborator, User $removedBy = null): bool
updateCollaboratorRole(Post $post, User $collaborator, string $newRole): bool

// Comments
addComment(Post $post, User $user, string $content, ?string $section = null): CollaborationComment
replyToComment(CollaborationComment $comment, User $user, string $content): CollaborationComment
resolveComment(CollaborationComment $comment, User $user): void
getUnresolvedComments(Post $post): Collection

// Versions
savePostVersion(Post $post, User $user, string $changeType, ?string $summary = null): PostVersion
restoreVersion(PostVersion $version, User $user): Post
getVersionHistory(Post $post, int $limit = 50): Paginated

// Statistics & History
getCollaborationStats(Post $post): array
getCollaborationHistory(Post $post, int $limit = 50): Collection

// Permissions
canManageCollaborators(Post $post, User $user): bool
canViewComments(Post $post, User $user): bool
canAddComments(Post $post, User $user): bool
```

## Model Methods

### Post Model

```php
// Collaboration relationships
$post->collaborators()
$post->activeCollaborators()
$post->collaboratorInvitations()
$post->collaborationComments()
$post->versions()
$post->collaborationActivities()

// Helper methods
$post->isCollaborative(): bool
$post->addCollaborator(User $user, string $role): PostCollaborator
$post->getCollaborator(User $user): ?PostCollaborator
$post->hasCollaborator(User $user): bool
$post->canBeEditedBy(User $user): bool
$post->canBeReviewedBy(User $user): bool
$post->getCollaborators(): Collection
$post->recordVersion(User $user, string $changeType = 'manual_save', ?string $summary = null): PostVersion
```

### User Model

```php
// Collaboration relationships
$user->collaborations()
$user->collaboratedPosts()
$user->receivedInvitations()
$user->sentInvitations()
$user->collaborationComments()
$user->collaborationActivities()

// Helper methods
$user->getCollaboratedPosts(int $limit = 10): Collection
$user->getPendingInvitations(int $limit = 10): Collection
$user->getActiveCollaborations(): Collection
```

## Integration Points

### In Post Creation/Edit Page

```blade
<!-- Collaboration Panel (for author only) -->
@if(Auth::id() === $post->author_id)
    <div class="mt-8 border-t pt-8">
        <h2 class="text-2xl font-bold mb-4">Collaboration</h2>

        @livewire('collaboration-invite', ['post' => $post])
        @livewire('collaboration-manager', ['post' => $post])
    </div>
@endif
```

### In Post Show Page

```blade
<!-- Collaboration Info for Collaborators -->
@if(Auth::check() && $post->hasCollaborator(Auth::user()))
    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mb-6">
        <p class="text-sm text-blue-900 dark:text-blue-200">
            📝 You are collaborating on this post as a
            <strong>{{ $post->getCollaborator(Auth::user())->role }}</strong>
        </p>
    </div>
@endif
```

### Dashboard Page

```blade
<!-- Pending Collaborations -->
@if($pendingInvitations = Auth::user()->getPendingInvitations())
    <section class="mb-8">
        <h2 class="text-2xl font-bold mb-4">📬 Pending Collaborations</h2>
        <div class="grid gap-4">
            @foreach($pendingInvitations as $invitation)
                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                    <p class="font-medium">{{ $invitation->post->title }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $invitation->inviter->name }} invited you as {{ $invitation->role }}
                    </p>
                    <div class="mt-3 space-x-2">
                        <a href="{{ route('collaboration.invitation.accept', ['token' => $invitation->token]) }}"
                           class="btn btn-sm btn-success">Accept</a>
                        <form method="POST" action="{{ route('collaboration.invitation.decline', $invitation) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-ghost">Decline</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

<!-- Active Collaborations -->
@if($collaborations = Auth::user()->getActiveCollaborations())
    <section class="mb-8">
        <h2 class="text-2xl font-bold mb-4">👥 Active Collaborations</h2>
        <div class="grid gap-4">
            @foreach($collaborations as $collaboration)
                <div class="p-4 bg-white dark:bg-slate-800 rounded-lg border">
                    <p class="font-medium">{{ $collaboration->post->title }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Role: {{ $collaboration->role }}</p>
                    <a href="{{ route('posts.edit', $collaboration->post) }}" class="btn btn-sm btn-primary mt-3">
                        Edit Post
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif
```

## Authorization & Policies

The system uses Laravel Policies for authorization:

```php
// PostPolicy methods
$user->can('view-collaborations', $post)
$user->can('manage-collaborators', $post)
```

### Custom Middleware (Optional)

Create a middleware to automatically load collaboration data:

```php
// app/Http/Middleware/LoadCollaborationData.php
public function handle(Request $request, Closure $next)
{
    if (Auth::check()) {
        Auth::user()->load(['collaborations', 'receivedInvitations']);
    }

    return $next($request);
}
```

## Configuration

In `.env`, you can configure:

```env
# Invitation expiry days
COLLABORATION_INVITATION_EXPIRY_DAYS=7

# Auto-save version interval (minutes)
COLLABORATION_AUTO_SAVE_INTERVAL=5

# Max collaborators per post (null for unlimited)
COLLABORATION_MAX_COLLABORATORS=null
```

## Best Practices

### 1. **Invite Early**
Invite collaborators before starting to write, so they can contribute from the beginning.

### 2. **Use Comments for Feedback**
Instead of editing content directly, leave comments for the author to review.

### 3. **Save Versions Regularly**
Click "Save" regularly so you have restore points. The system auto-saves every 5 minutes.

### 4. **Resolve Comments**
Mark comments as resolved once changes are made to keep track of outstanding feedback.

### 5. **Check Activity Log**
Use the collaboration history to see who did what and when.

## Performance Optimization

### Database Indexes
The migration includes strategic indexes:

```sql
CREATE UNIQUE INDEX post_collaborators_unique
    ON post_collaborators(post_id, user_id);

CREATE INDEX collaboration_invitations_email
    ON collaboration_invitations(email);

CREATE INDEX collaboration_activities_created_at
    ON collaboration_activities(created_at);
```

### Query Optimization

Use eager loading:

```php
$posts = Post::with('collaborators.user', 'versions.editor')
    ->with('collaborationComments.user')
    ->get();
```

Use scopes:

```php
$collaborators = $post->activeCollaborators()->with('user')->get();
$versions = $post->versions()->latest('created_at')->paginate(20);
```

## Future Enhancements

1. **Real-time Collaboration**
   - Live editing with Pusher/Broadcast
   - Real-time cursor positions
   - Instant comments

2. **Advanced Features**
   - Suggestion mode (like Google Docs)
   - Track changes highlighting
   - Approval workflows

3. **Integration**
   - Slack/Discord notifications
   - Email digests
   - Calendar events

4. **Analytics**
   - Collaboration metrics
   - Contributor statistics
   - Writing patterns

## Troubleshooting

### Invitation Not Appearing
- Check token hasn't expired (default 7 days)
- Verify email matches user account
- Check user role permissions

### Comments Not Showing
- Ensure user has 'review' permission
- Check collaboration comments aren't deleted
- Verify post exists

### Version Restore Failing
- Ensure you have 'edit' permission
- Check post content isn't null
- Verify version exists in database

## Support

For issues or questions:
1. Check this guide
2. Review test files in `tests/Feature/CollaborationTest.php`
3. Check Laravel Policies documentation
4. Review model relationships and scopes

---

**Last Updated:** November 8, 2025
**Version:** 1.0
**Status:** Complete and Production Ready
