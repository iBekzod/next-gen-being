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
        Schema::create('digital_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();

            // Product Type
            $table->enum('type', ['prompt', 'template', 'tutorial', 'course', 'cheatsheet', 'code_example']);

            // Pricing
            $table->decimal('price', 10, 2); // $2.00 - $99.99
            $table->decimal('original_price', 10, 2)->nullable(); // For sales

            // Access Control
            $table->enum('tier_required', ['free', 'basic', 'pro', 'team'])->default('free');
            $table->boolean('is_free')->default(false);

            // Files & Media
            $table->string('file_path')->nullable(); // Main downloadable file
            $table->string('preview_file_path')->nullable(); // Preview/sample
            $table->json('files')->nullable(); // Multiple files: [{name, path, size, type}]
            $table->string('thumbnail')->nullable();
            $table->json('gallery')->nullable(); // Screenshots

            // Metadata
            $table->json('tags')->nullable(); // ['chatgpt', 'prompt-engineering']
            $table->string('category')->nullable();
            $table->integer('downloads_count')->default(0);
            $table->integer('purchases_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0); // 0.00-5.00
            $table->integer('reviews_count')->default(0);

            // Content Preview
            $table->longText('content')->nullable(); // Preview content (first 30%)
            $table->json('features')->nullable(); // ['Feature 1', 'Feature 2']
            $table->json('includes')->nullable(); // ['PDF file', 'TXT file', 'Code examples']

            // SEO
            $table->json('seo_meta')->nullable(); // {title, description, keywords}

            // Publishing
            $table->enum('status', ['draft', 'pending_review', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Revenue Share
            $table->decimal('revenue_share_percentage', 5, 2)->default(70.00); // 70% to creator

            // LemonSqueezy
            $table->string('lemonsqueezy_product_id')->nullable();
            $table->string('lemonsqueezy_variant_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['type', 'tier_required']);
            $table->index('creator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_products');
    }
};
