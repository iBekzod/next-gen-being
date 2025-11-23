<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['reading', 'writing']); // reading or writing
            $table->integer('current_count')->default(0);
            $table->integer('longest_count')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->timestamp('broken_at')->nullable();
            $table->json('metadata')->nullable(); // For storing extra data

            $table->timestamps();

            $table->unique(['user_id', 'type']);
            $table->index('user_id');
            $table->index('type');
            $table->index('current_count');
            $table->index('longest_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streaks');
    }
};
