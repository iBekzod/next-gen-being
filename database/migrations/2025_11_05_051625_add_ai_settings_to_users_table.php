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
        Schema::table('users', function (Blueprint $table) {
            // AI subscription tier
            $table->enum('ai_tier', ['free', 'basic', 'premium', 'enterprise'])->default('free')->after('is_active');

            // Store encrypted API keys for bloggers who use their own
            $table->text('groq_api_key')->nullable()->after('ai_tier');
            $table->text('openai_api_key')->nullable()->after('groq_api_key');
            $table->text('unsplash_api_key')->nullable()->after('openai_api_key');

            // AI usage tracking
            $table->integer('ai_posts_generated')->default(0)->after('unsplash_api_key');
            $table->integer('ai_images_generated')->default(0)->after('ai_posts_generated');
            $table->timestamp('ai_tier_starts_at')->nullable()->after('ai_images_generated');
            $table->timestamp('ai_tier_expires_at')->nullable()->after('ai_tier_starts_at');

            // AI quota limits per month (null = unlimited for premium)
            $table->integer('monthly_ai_posts_limit')->nullable()->after('ai_tier_expires_at');
            $table->integer('monthly_ai_images_limit')->nullable()->after('monthly_ai_posts_limit');

            // Reset counters each month
            $table->date('ai_usage_reset_date')->nullable()->after('monthly_ai_images_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ai_tier',
                'groq_api_key',
                'openai_api_key',
                'unsplash_api_key',
                'ai_posts_generated',
                'ai_images_generated',
                'ai_tier_starts_at',
                'ai_tier_expires_at',
                'monthly_ai_posts_limit',
                'monthly_ai_images_limit',
                'ai_usage_reset_date',
            ]);
        });
    }
};
