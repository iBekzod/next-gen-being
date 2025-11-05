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
        Schema::create('video_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Video specifications
            $table->string('video_type', 20); // youtube, tiktok, reel, short
            $table->integer('duration_seconds')->nullable();
            $table->string('resolution', 20)->nullable(); // 1920x1080, 1080x1920

            // Generation process assets
            $table->text('script')->nullable(); // AI-generated script with timestamps
            $table->text('voiceover_url')->nullable(); // Generated audio file URL
            $table->jsonb('video_clips')->nullable(); // Stock footage used [{"url": "...", "start": 0, "duration": 5}]
            $table->text('captions_url')->nullable(); // WebVTT subtitle file URL

            // Final output
            $table->text('video_url')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->decimal('file_size_mb', 10, 2)->nullable();

            // AI credits and costs
            $table->integer('ai_credits_used')->default(0);
            $table->decimal('generation_cost', 10, 2)->default(0);

            // Processing status
            $table->string('status', 20)->default('queued'); // queued, processing, completed, failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['post_id', 'video_type']);
            $table->index(['status', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_generations');
    }
};
