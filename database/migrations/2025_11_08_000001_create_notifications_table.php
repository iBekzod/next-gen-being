<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index(); // User who triggered the notification
            $table->enum('type', [
                'comment_reply',      // Someone replied to your comment
                'post_liked',         // Someone liked your post
                'post_commented',     // Someone commented on your post
                'comment_liked',      // Someone liked your comment
                'user_followed',      // Someone followed you
                'post_published',     // Your scheduled post was published
                'premium_access',     // You got premium access
                'payout_completed',   // Your payout was completed
                'earnings_milestone', // You reached earnings milestone
                'mention',            // You were mentioned in a comment
                'newsletter_open',    // Newsletter was opened
                'social_share',       // Your content was shared
            ]);
            $table->morphs('notifiable'); // Polymorphic relation (Post, Comment, User, etc.)
            $table->string('title');
            $table->text('message');
            $table->text('action_url')->nullable(); // Where to redirect
            $table->text('data')->nullable(); // JSON extra data
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Index for quick "unread" queries
            $table->index(['user_id', 'read_at']);
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();

            // Email notifications
            $table->boolean('email_comment_reply')->default(true);
            $table->boolean('email_post_liked')->default(false);
            $table->boolean('email_post_commented')->default(true);
            $table->boolean('email_user_followed')->default(true);
            $table->boolean('email_mention')->default(true);

            // In-app notifications
            $table->boolean('app_comment_reply')->default(true);
            $table->boolean('app_post_liked')->default(true);
            $table->boolean('app_post_commented')->default(true);
            $table->boolean('app_user_followed')->default(true);
            $table->boolean('app_mention')->default(true);

            // Digest
            $table->enum('digest_frequency', ['instant', 'daily', 'weekly', 'never'])->default('instant');
            $table->boolean('digest_enabled')->default(true);

            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
    }
};
