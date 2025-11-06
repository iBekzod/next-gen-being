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
        Schema::create('job_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique();
            $table->string('type', 50); // 'video-generation', 'social-media-publish', etc.
            $table->string('queue', 50)->default('default');
            $table->string('status', 20)->default('pending'); // pending, processing, completed, failed
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->morphs('trackable'); // post_id, video_generation_id, etc.
            $table->integer('progress')->default(0); // 0-100
            $table->text('progress_message')->nullable();
            $table->jsonb('metadata')->nullable(); // Additional context data
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'type', 'status']);
            // Note: morphs() already creates index for trackable_type and trackable_id
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_statuses');
    }
};
