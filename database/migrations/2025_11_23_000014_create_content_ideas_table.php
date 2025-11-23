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
        Schema::create('content_ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->index();
            $table->text('description');
            $table->string('topic')->index();
            $table->string('content_type')->index();
            $table->string('target_audience')->nullable();
            $table->json('keywords')->nullable();
            $table->json('outline')->nullable();
            $table->enum('status', ['active', 'in_progress', 'completed', 'archived'])->default('active')->index();
            $table->enum('source', ['ai_generated', 'trending_analysis', 'manual'])->default('manual');
            $table->unsignedInteger('trending_score')->default(50);
            $table->unsignedInteger('difficulty_score')->default(50);
            $table->unsignedInteger('estimated_read_time')->default(5);
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->index();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'topic']);
            $table->index(['status', 'trending_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_ideas');
    }
};
