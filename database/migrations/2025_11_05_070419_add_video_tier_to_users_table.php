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
            // Video subscription tier
            $table->string('video_tier', 20)->default('free')->after('ai_usage_reset_date'); // free, video_pro

            // Video usage tracking
            $table->integer('videos_generated')->default(0)->after('video_tier');
            $table->integer('monthly_video_limit')->nullable()->after('videos_generated'); // null = unlimited for video_pro
            $table->timestamp('video_tier_starts_at')->nullable()->after('monthly_video_limit');
            $table->timestamp('video_tier_expires_at')->nullable()->after('video_tier_starts_at');

            // Custom branding for Video Pro tier
            $table->text('custom_video_intro_url')->nullable()->after('video_tier_expires_at');
            $table->text('custom_video_outro_url')->nullable()->after('custom_video_intro_url');
            $table->text('custom_video_logo_url')->nullable()->after('custom_video_outro_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'video_tier',
                'videos_generated',
                'monthly_video_limit',
                'video_tier_starts_at',
                'video_tier_expires_at',
                'custom_video_intro_url',
                'custom_video_outro_url',
                'custom_video_logo_url',
            ]);
        });
    }
};
