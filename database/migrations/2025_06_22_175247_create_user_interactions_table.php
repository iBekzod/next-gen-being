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
        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('interactable'); // posts, comments
            $table->enum('type', ['like', 'bookmark', 'view', 'share']);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'interactable_id', 'interactable_type', 'type'], 'user_interactions_unique');
            $table->index(['interactable_id', 'interactable_type', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_interactions');
    }
};
