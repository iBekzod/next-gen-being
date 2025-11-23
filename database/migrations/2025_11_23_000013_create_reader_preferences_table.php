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
        Schema::create('reader_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->json('preferred_categories')->nullable()->comment('Category IDs with weights');
            $table->json('preferred_authors')->nullable()->comment('Author user IDs with weights');
            $table->json('preferred_tags')->nullable()->comment('Tag names with weights');
            $table->json('disliked_categories')->nullable()->comment('Category IDs to filter out');
            $table->json('disliked_authors')->nullable()->comment('Author user IDs to filter out');
            $table->json('disliked_tags')->nullable()->comment('Tag names to filter out');
            $table->json('content_type_preferences')->nullable()->comment('Content type scores');
            $table->json('reading_patterns')->nullable()->comment('Day/hour preferences');
            $table->json('engagement_data')->nullable()->comment('Engagement metrics');
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('last_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reader_preferences');
    }
};
