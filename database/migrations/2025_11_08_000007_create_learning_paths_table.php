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
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('goal')->nullable();
            $table->enum('skill_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
            $table->integer('estimated_duration_hours')->default(0);
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->boolean('ai_generated')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('ai_generated');
            $table->index('skill_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_paths');
    }
};
