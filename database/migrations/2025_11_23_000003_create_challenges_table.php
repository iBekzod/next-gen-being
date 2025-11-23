<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['reading', 'writing', 'engagement', 'community']);
            $table->integer('target_value'); // e.g., 10 for "read 10 posts"
            $table->integer('reward_points')->default(0);
            $table->string('reward_description')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('icon')->nullable(); // Icon emoji or URL
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
            $table->index('starts_at');
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
