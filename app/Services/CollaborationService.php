<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\PostCollaborator;
use App\Models\CollaborationInvitation;
use App\Models\CollaborationComment;
use App\Models\CollaborationActivity;
use App\Models\PostVersion;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

class CollaborationService
{
    /**
     * Invite a user to collaborate on a post
     */
    public function inviteCollaborator(Post $post, User $inviter, string $email, string $role = 'editor', int $expiresInDays = 7): CollaborationInvitation
    {
        // Check if already a collaborator
        $user = User::where('email', $email)->first();
        if ($user && $post->hasCollaborator($user)) {
            throw new \Exception("User is already a collaborator on this post");
        }

        // Create invitation
        $invitation = CollaborationInvitation::create([
            'post_id' => $post->id,
            'inviter_id' => $inviter->id,
            'email' => $email,
            'user_id' => $user?->id,
            'role' => $role,
            'token' => CollaborationInvitation::generateToken(),
            'expires_at' => now()->addDays($expiresInDays),
        ]);

        // Log activity
        CollaborationActivity::logActivity(
            $post,
            $inviter,
            'invited',
            "{$inviter->name} invited {$email} as {$role}"
        );

        return $invitation;
    }

    /**
     * Accept a collaboration invitation
     */
    public function acceptInvitation(CollaborationInvitation $invitation, User $user): bool
    {
        if (!$invitation->isValid()) {
            return false;
        }

        // Update invitation user if not already set
        if (!$invitation->user_id) {
            $invitation->update(['user_id' => $user->id]);
        }

        // Add as collaborator and mark invitation as accepted
        $invitation->accept();

        // Log activity
        CollaborationActivity::logActivity(
            $invitation->post,
            $user,
            'joined',
            "{$user->name} accepted invitation to collaborate"
        );

        return true;
    }

    /**
     * Decline a collaboration invitation
     */
    public function declineInvitation(CollaborationInvitation $invitation): bool
    {
        return $invitation->decline();
    }

    /**
     * Remove a collaborator from a post
     */
    public function removeCollaborator(Post $post, User $collaborator, ?User $removedBy = null): bool
    {
        $collaboratorRecord = $post->getCollaborator($collaborator);

        if (!$collaboratorRecord) {
            return false;
        }

        $removedBy = $removedBy ?? auth()->user();

        $collaboratorRecord->leave();

        // Log activity
        CollaborationActivity::logActivity(
            $post,
            $removedBy,
            'left',
            "{$collaborator->name} was removed from collaboration"
        );

        return true;
    }

    /**
     * Update collaborator role
     */
    public function updateCollaboratorRole(Post $post, User $collaborator, string $newRole, ?User $changedBy = null): bool
    {
        $collaboratorRecord = $post->getCollaborator($collaborator);

        if (!$collaboratorRecord || !$collaboratorRecord->isActive()) {
            return false;
        }

        $changedBy = $changedBy ?? auth()->user();
        $oldRole = $collaboratorRecord->role;

        $collaboratorRecord->updateRole($newRole);

        // Log activity
        CollaborationActivity::logActivity(
            $post,
            $changedBy,
            'role_changed',
            "{$collaborator->name} role changed from {$oldRole} to {$newRole}"
        );

        return true;
    }

    /**
     * Add a comment to a post section
     */
    public function addComment(Post $post, User $user, string $content, ?string $section = null, ?int $lineNumber = null): CollaborationComment
    {
        $comment = CollaborationComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $content,
            'section' => $section,
            'line_number' => $lineNumber,
        ]);

        // Log activity
        CollaborationActivity::logActivity(
            $post,
            $user,
            'comment_added',
            "{$user->name} added a comment"
        );

        return $comment;
    }

    /**
     * Reply to a collaboration comment
     */
    public function replyToComment(CollaborationComment $parentComment, User $user, string $content): CollaborationComment
    {
        $reply = CollaborationComment::create([
            'post_id' => $parentComment->post_id,
            'user_id' => $user->id,
            'content' => $content,
            'parent_comment_id' => $parentComment->id,
        ]);

        return $reply;
    }

    /**
     * Resolve a collaboration comment
     */
    public function resolveComment(CollaborationComment $comment, User $user): void
    {
        $comment->resolve($user);

        // Log activity
        CollaborationActivity::logActivity(
            $comment->post,
            $user,
            'comment_resolved',
            "{$user->name} resolved a comment"
        );
    }

    /**
     * Get unresolved comments for a post
     */
    public function getUnresolvedComments(Post $post): Collection
    {
        return $post->collaborationComments()
                    ->whereIn('status', ['open', 'needs_discussion'])
                    ->topLevel()
                    ->with('user', 'replies.user')
                    ->get();
    }

    /**
     * Save a post version
     */
    public function savePostVersion(Post $post, User $user, string $changeType = 'manual_save', ?string $summary = null): PostVersion
    {
        $version = $post->recordVersion($user, $changeType, $summary);

        // Log activity
        CollaborationActivity::logActivity(
            $post,
            $user,
            'version_created',
            "{$user->name} saved version"
        );

        return $version;
    }

    /**
     * Restore a previous version
     */
    public function restoreVersion(PostVersion $version, User $user): Post
    {
        $version->restore();

        // Create new version marking the restoration
        PostVersion::create([
            'post_id' => $version->post_id,
            'edited_by' => $user->id,
            'title' => $version->post->title,
            'content' => $version->post->content,
            'content_json' => $version->post->content_json,
            'change_type' => 'manual_save',
            'change_summary' => "Restored version from {$version->created_at->format('M d, Y H:i')}",
            'created_at' => now(),
        ]);

        // Log activity
        CollaborationActivity::logActivity(
            $version->post,
            $user,
            'version_created',
            "{$user->name} restored a previous version"
        );

        return $version->post->refresh();
    }

    /**
     * Get post collaboration history/activity
     */
    public function getCollaborationHistory(Post $post, int $limit = 50): Collection
    {
        return $post->collaborationActivities()
                    ->with('user')
                    ->latest('created_at')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get version history for a post
     */
    public function getVersionHistory(Post $post, int $limit = 50)
    {
        return $post->versions()
                    ->with('editor')
                    ->latest('created_at')
                    ->limit($limit)
                    ->paginate($limit);
    }

    /**
     * Get collaboration statistics for a post
     */
    public function getCollaborationStats(Post $post): array
    {
        return [
            'total_collaborators' => $post->activeCollaborators()->count(),
            'total_editors' => $post->activeCollaborators()->byRole('editor')->count(),
            'total_reviewers' => $post->activeCollaborators()->byRole('reviewer')->count(),
            'total_viewers' => $post->activeCollaborators()->byRole('viewer')->count(),
            'total_comments' => $post->collaborationComments()->count(),
            'open_comments' => $post->collaborationComments()->open()->count(),
            'total_versions' => $post->versions()->count(),
            'last_edited_at' => $post->versions()->latest('created_at')->first()?->created_at,
            'last_edited_by' => $post->versions()->latest('created_at')->first()?->editor,
        ];
    }

    /**
     * Check if a user can manage collaborators for a post
     */
    public function canManageCollaborators(Post $post, User $user): bool
    {
        if ($post->author_id === $user->id) {
            return true;
        }

        $collaborator = $post->getCollaborator($user);
        return $collaborator && $collaborator->isOwner();
    }

    /**
     * Check if a user can view collaboration comments
     */
    public function canViewComments(Post $post, User $user): bool
    {
        if ($post->author_id === $user->id) {
            return true;
        }

        return $post->hasCollaborator($user);
    }

    /**
     * Check if a user can add comments
     */
    public function canAddComments(Post $post, User $user): bool
    {
        return $post->canBeReviewedBy($user);
    }

    /**
     * Send pending invitation reminders
     */
    public function sendInvitationReminders(): int
    {
        $reminderThreshold = now()->subDays(3);

        $invitations = CollaborationInvitation::pending()
                    ->where('created_at', '<', $reminderThreshold)
                    ->where('expires_at', '>', now())
                    ->get();

        $count = 0;
        foreach ($invitations as $invitation) {
            // Here you would send a reminder email
            // For now, just count
            $count++;
        }

        return $count;
    }

    /**
     * Clean up expired invitations
     */
    public function cleanupExpiredInvitations(): int
    {
        $deleted = CollaborationInvitation::expired()
                    ->where('status', 'pending')
                    ->delete();

        return $deleted;
    }
}
