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
        Schema::create('social_media_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('social_media_account_id')->nullable()->constrained()->onDelete('set null');

            // Platform details
            $table->string('platform', 50); // youtube, instagram, twitter, etc.
            $table->string('platform_post_id')->nullable(); // ID from the social media platform
            $table->text('platform_post_url')->nullable();

            // Content variants
            $table->text('content_text')->nullable();
            $table->text('content_media_url')->nullable(); // URL to image or video
            $table->string('content_type', 20)->default('text'); // text, image, video, carousel

            // Metadata
            $table->text('caption')->nullable();
            $table->text('hashtags')->nullable(); // JSON array of hashtags
            $table->text('mentions')->nullable(); // JSON array of mentions

            // Engagement metrics (synced periodically from platform APIs)
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->bigInteger('views_count')->default(0);

            // Publishing status
            $table->string('status', 20)->default('draft'); // draft, scheduled, published, failed
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['post_id', 'platform']);
            $table->index(['status', 'scheduled_at']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_posts');
    }
};
