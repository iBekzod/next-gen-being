<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Achievement definitions
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('icon'); // emoji or icon name
            $table->string('color')->default('blue'); // badge color
            $table->string('category'); // learning, engagement, milestone, etc.
            $table->integer('points')->default(10); // Points for earning this
            $table->json('conditions'); // JSON with achievement conditions
            $table->timestamps();
        });

        // User achievements earned
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained('achievements')->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->timestamps();

            // Unique per user per achievement
            $table->unique(['user_id', 'achievement_id']);
            $table->index(['user_id', 'earned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
    }
};
