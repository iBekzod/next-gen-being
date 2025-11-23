<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('progress')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('reward_claimed')->default(false);
            $table->timestamp('claimed_at')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(['challenge_id', 'user_id']);
            $table->index('user_id');
            $table->index('challenge_id');
            $table->index('is_completed');
            $table->index('reward_claimed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_participants');
    }
};
