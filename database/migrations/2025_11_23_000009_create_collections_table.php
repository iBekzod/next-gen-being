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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug')->unique()->index();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->boolean('is_public')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('saved_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['is_public', 'created_at']);
            $table->index(['is_featured', 'view_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
