<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('demo_type', ['static', 'external', 'none'])->default('none');
            $table->string('demo_path')->nullable();   // public-disk path for static demos
            $table->string('demo_url')->nullable();     // for external demos
            $table->enum('status', ['draft', 'pending_review', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->enum('plagiarism_status', ['unchecked', 'checked', 'flagged'])->default('unchecked');
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('sales_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_listings');
    }
};
