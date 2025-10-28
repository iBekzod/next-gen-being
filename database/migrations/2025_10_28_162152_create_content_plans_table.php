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
        Schema::create('content_plans', function (Blueprint $table) {
            $table->id();
            $table->string('month'); // e.g., "2025-11"
            $table->string('theme'); // Monthly theme like "AI & Machine Learning", "Cloud Architecture"
            $table->text('description')->nullable(); // Theme description
            $table->json('planned_topics'); // Array of planned topics with categories
            $table->json('generated_topics')->nullable(); // Track what was actually generated
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->timestamps();

            $table->unique('month'); // One plan per month
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_plans');
    }
};
