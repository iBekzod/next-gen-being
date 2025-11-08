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
        if (Schema::hasTable('ai_recommendations')) {
            return;
        }

        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('learning_path_id')->nullable()->constrained('learning_paths')->onDelete('cascade');
            $table->foreignId('post_id')->nullable()->constrained('posts')->onDelete('cascade');
            $table->enum('recommendation_type', ['next_tutorial', 'skill_gap', 'related_content', 'challenge', 'review'])->default('next_tutorial');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('reason')->nullable();
            $table->decimal('confidence_score', 3, 2)->default(0.5); // 0.0 to 1.0
            $table->json('metadata')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('acted_on_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('learning_path_id');
            $table->index('post_id');
            $table->index('recommendation_type');
            $table->index(['user_id', 'dismissed_at']);
            $table->index('confidence_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
