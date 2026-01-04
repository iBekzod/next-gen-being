<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutorial_collections', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500);
            $table->string('slug', 500)->unique();
            $table->text('description');
            $table->string('topic', 255);

            // Sourcing
            $table->json('source_ids')->comment('Content source IDs');
            $table->json('collected_content_ids')->comment('Collected content record IDs');
            $table->json('references')->nullable()->comment('Citation array');

            // Compilation
            $table->json('steps')->nullable()->comment('Tutorial steps compiled from sources');
            $table->json('code_examples')->nullable()->comment('Code examples and snippets');
            $table->json('best_practices')->nullable()->comment('Distilled best practices');
            $table->json('common_pitfalls')->nullable()->comment('Common mistakes/warnings');

            // Metadata
            $table->enum('skill_level', ['beginner', 'intermediate', 'advanced'])->default('intermediate');
            $table->string('language', 5)->default('en');
            $table->integer('estimated_hours')->nullable();
            $table->integer('reading_time_minutes')->nullable();

            // Content
            $table->longText('compiled_content')->nullable()->comment('Final compiled tutorial');
            $table->string('featured_image', 2048)->nullable();

            // Publishing
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();

            // Engagement
            $table->integer('view_count')->default(0);
            $table->integer('share_count')->default(0);
            $table->integer('completion_count')->default(0);

            $table->timestamps();

            $table->index('topic');
            $table->index('skill_level');
            $table->index('status');
            $table->index('language');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutorial_collections');
    }
};
