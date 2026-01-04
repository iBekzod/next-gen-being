<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('url', 2048);
            $table->enum('category', ['news', 'blog', 'research', 'announcement', 'social']);
            $table->string('language', 5)->default('en');
            $table->integer('trust_level')->default(100)->comment('0-100 trust score');
            $table->boolean('scraping_enabled')->default(true);
            $table->timestamp('last_scraped_at')->nullable();
            $table->text('description')->nullable();
            $table->string('css_selectors', 2048)->nullable()->comment('JSON with CSS selectors for content extraction');
            $table->integer('rate_limit_per_sec')->default(1)->comment('Polite scraping rate limit');
            $table->timestamps();

            $table->index('category');
            $table->index('scraping_enabled');
            $table->index('last_scraped_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_sources');
    }
};
