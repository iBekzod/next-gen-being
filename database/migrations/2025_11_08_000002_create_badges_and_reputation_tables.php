<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Badges table - predefined badges users can earn
        if (!Schema::hasTable('badges')) {
            Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Rising Star", "Top Contributor", etc.
            $table->text('description');
            $table->string('slug')->unique();
            $table->string('icon'); // Emoji or icon file
            $table->string('color')->default('blue'); // For styling
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('requirements')->nullable(); // {"min_posts": 10, "min_likes": 100}
            $table->timestamps();
        });
        }

        // User badges - tracks which badges a user has earned
        if (!Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('badge_id');
            $table->timestamp('earned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('badge_id')->references('id')->on('badges')->onDelete('cascade');
            $table->index('earned_at');
            });
        }

        // User reputation - tracks reputation points
        if (!Schema::hasTable('user_reputation')) {
            Schema::create('user_reputation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->integer('points')->default(0); // Total reputation points
            $table->integer('posts_published')->default(0);
            $table->integer('posts_liked')->default(0);
            $table->integer('comments_received')->default(0);
            $table->integer('followers_count')->default(0);
            $table->integer('engagement_score')->default(0); // Calculated from interactions
            $table->string('level')->default('beginner'); // beginner, intermediate, advanced, expert, legend
            $table->integer('level_progress')->default(0); // % to next level
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('points');
            $table->index('level');
            $table->index('engagement_score');
            });
        }

        // Achievement tracking - for future use
        if (!Schema::hasTable('user_achievements')) {
            Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('achievement_code'); // 'first_post', 'first_like_received', 'reached_100_followers'
            $table->text('description');
            $table->timestamp('achieved_at')->useCurrent();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'achievement_code']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('achievement_code');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('user_reputation');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
