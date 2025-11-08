<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tutorial_progress')) {
            return;
        }

        Schema::create('tutorial_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->string('series_slug');
            $table->integer('series_part');
            $table->boolean('completed')->default(false);
            $table->integer('read_count')->default(0); // Track how many times they read it
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_minutes')->default(0); // Track time spent reading
            $table->timestamps();

            // Ensure unique progress per user per post
            $table->unique(['user_id', 'post_id']);
            $table->index(['user_id', 'series_slug']);
            $table->index(['user_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutorial_progress');
    }
};
