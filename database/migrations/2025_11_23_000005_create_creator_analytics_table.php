<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');

            // Content Metrics
            $table->integer('posts_published')->default(0);
            $table->integer('posts_views')->default(0);
            $table->integer('posts_likes')->default(0);
            $table->integer('posts_comments')->default(0);
            $table->integer('posts_shares')->default(0);

            // Audience Metrics
            $table->integer('followers_gained')->default(0);
            $table->integer('followers_lost')->default(0);

            // Revenue Metrics
            $table->integer('tips_received')->default(0);
            $table->decimal('tips_amount', 10, 2)->default(0);
            $table->decimal('subscription_revenue', 10, 2)->default(0);

            // Engagement Metrics
            $table->integer('total_engagement')->default(0);
            $table->decimal('engagement_rate', 8, 4)->default(0);
            $table->integer('average_read_time')->default(0);
            $table->decimal('bounce_rate', 8, 4)->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index('user_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_analytics');
    }
};
