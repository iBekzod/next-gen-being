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
        // Track analytics snapshots for posts (daily aggregation)
        if (!Schema::hasTable('post_analytics')) {
            Schema::create('post_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->date('date')->index(); // Track per day
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->unsignedInteger('unique_readers')->default(0); // Unique IPs/sessions per day
            $table->unsignedInteger('avg_read_time')->default(0); // In seconds
            $table->decimal('scroll_depth', 5, 2)->default(0); // Percentage of page scrolled
            $table->json('traffic_sources')->nullable(); // { 'direct': 10, 'search': 5, 'social': 3, 'referral': 2 }
            $table->json('top_referrers')->nullable(); // Top 5 referrer URLs
            $table->json('device_breakdown')->nullable(); // { 'mobile': 60, 'desktop': 35, 'tablet': 5 }
            $table->json('geo_data')->nullable(); // { 'US': 100, 'UK': 50, 'DE': 25 }
            $table->timestamps();

            $table->unique(['post_id', 'date']); // One analytics record per post per day
            $table->index(['post_id', 'date']);
            });
        }

        // Reader engagement data (for follower relationships)
        if (!Schema::hasTable('reader_engagements')) {
            Schema::create('reader_engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('reader_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('action', ['view', 'like', 'comment', 'share', 'bookmark', 'favorite'])->index();
            $table->string('reader_email')->nullable(); // For anonymous readers
            $table->string('reader_ip')->nullable(); // For tracking unique readers
            $table->json('metadata')->nullable(); // Additional data (scroll depth, time on page, etc)
            $table->timestamps();

            $table->index(['post_id', 'action']);
            $table->index(['reader_id', 'action']);
            $table->index('created_at');
            });
        }

        // Author stats cache (pre-computed for dashboard performance)
        if (!Schema::hasTable('author_stats')) {
            Schema::create('author_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('total_posts')->default(0);
            $table->unsignedInteger('total_views')->default(0);
            $table->unsignedInteger('total_likes')->default(0);
            $table->unsignedInteger('total_comments')->default(0);
            $table->unsignedInteger('total_followers')->default(0);
            $table->unsignedInteger('total_earnings')->default(0); // In cents
            $table->unsignedInteger('avg_post_views')->default(0);
            $table->decimal('engagement_rate', 5, 2)->default(0); // (likes+comments)/views * 100
            $table->date('last_post_date')->nullable();
            $table->json('top_topics')->nullable(); // Most popular tags/categories
            $table->json('monthly_growth')->nullable(); // { 'views': {...}, 'followers': {...} }
            $table->timestamps();

            $table->unique('author_id');
            $table->index('total_views');
            $table->index('engagement_rate');
            });
        }

        // Monthly performance summary (for insights and trends)
        if (!Schema::hasTable('post_monthly_stats')) {
            Schema::create('post_monthly_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->unsignedInteger('unique_readers')->default(0);
            $table->decimal('engagement_rate', 5, 2)->default(0); // Percentage
            $table->timestamps();

            $table->unique(['post_id', 'year', 'month']);
            $table->index(['post_id', 'year']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_monthly_stats');
        Schema::dropIfExists('author_stats');
        Schema::dropIfExists('reader_engagements');
        Schema::dropIfExists('post_analytics');
    }
};
