<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Post collaborators - track who can edit/view/review a post
        if (!Schema::hasTable('post_collaborators')) {
            Schema::create('post_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['owner', 'editor', 'reviewer', 'viewer'])->default('viewer');
            $table->json('permissions')->nullable(); // Custom permissions per role
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'user_id']); // Prevent duplicate collaborators
            $table->index('post_id');
            $table->index('user_id');
            });
        }

        // Collaboration invitations - pending invites to collaborators
        if (!Schema::hasTable('collaboration_invitations')) {
            Schema::create('collaboration_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('inviter_id')->constrained('users')->onDelete('cascade');
            $table->string('email'); // Email of person being invited
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // If they have account
            $table->enum('role', ['editor', 'reviewer', 'viewer'])->default('editor');
            $table->enum('status', ['pending', 'accepted', 'declined', 'cancelled'])->default('pending');
            $table->string('token')->unique(); // For email verification link
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamps();

            $table->index('post_id');
            $table->index('inviter_id');
            $table->index('user_id');
            $table->index('email');
            $table->index('token');
            });
        }

        // Collaboration comments - editorial comments on sections of posts
        if (!Schema::hasTable('collaboration_comments')) {
            Schema::create('collaboration_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->string('section')->nullable(); // paragraph id, section name, or line reference
            $table->unsignedInteger('line_number')->nullable(); // For specific line references
            $table->string('status')->default('open'); // open, resolved, needs_discussion
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('parent_comment_id')->nullable()->constrained('collaboration_comments')->onDelete('cascade'); // For nested replies
            $table->timestamps();

            $table->index('post_id');
            $table->index('user_id');
            $table->index('status');
            });
        }

        // Post versions/revisions - track collaborative editing history
        if (!Schema::hasTable('post_versions')) {
            Schema::create('post_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('edited_by')->constrained('users')->onDelete('cascade');
            $table->text('title');
            $table->longText('content');
            $table->longText('content_json')->nullable();
            $table->string('change_summary')->nullable(); // Brief description of what changed
            $table->enum('change_type', ['auto_save', 'manual_save', 'published', 'scheduled'])->default('manual_save');
            $table->json('changes_metadata')->nullable(); // Track specific changes made
            $table->timestamp('created_at');

            $table->index('post_id');
            $table->index('edited_by');
            $table->index('created_at');
            });
        }

        // Collaboration activity log - for audit trail
        if (!Schema::hasTable('collaboration_activities')) {
            Schema::create('collaboration_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', [
                'invited',
                'joined',
                'left',
                'role_changed',
                'content_edited',
                'comment_added',
                'comment_resolved',
                'version_created',
                'published'
            ]);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Store additional context
            $table->timestamps();

            $table->index('post_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaboration_activities');
        Schema::dropIfExists('post_versions');
        Schema::dropIfExists('collaboration_comments');
        Schema::dropIfExists('collaboration_invitations');
        Schema::dropIfExists('post_collaborators');
    }
};
