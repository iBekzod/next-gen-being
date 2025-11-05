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
        Schema::table('posts', function (Blueprint $table) {
            // Post type classification
            $table->string('post_type', 20)->default('article')->after('status'); // article, visual_story, video_blog

            // Video-specific fields
            $table->text('video_url')->nullable()->after('featured_image');
            $table->integer('video_duration')->nullable()->after('video_url'); // Duration in seconds
            $table->text('video_thumbnail')->nullable()->after('video_duration');
            $table->text('video_captions_url')->nullable()->after('video_thumbnail'); // WebVTT subtitle file

            // Index for post type filtering
            $table->index('post_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['post_type']);
            $table->dropColumn([
                'post_type',
                'video_url',
                'video_duration',
                'video_thumbnail',
                'video_captions_url',
            ]);
        });
    }
};
