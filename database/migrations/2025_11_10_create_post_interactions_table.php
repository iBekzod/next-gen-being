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
        Schema::create('post_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'like', 'view', 'comment', 'share'
            $table->json('metadata')->nullable(); // Additional data like IP, device, etc.
            $table->timestamps();

            // Indexes for fast lookups
            $table->index(['user_id', 'type']);
            $table->index(['post_id', 'type']);
            $table->index(['created_at']);

            // Unique constraint to prevent duplicate interactions
            $table->unique(['user_id', 'post_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_interactions');
    }
};
