<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collected_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_source_id')->constrained('content_sources')->onDelete('cascade');
            $table->string('external_url', 2048)->unique();
            $table->string('title', 500);
            $table->text('excerpt');
            $table->longText('full_content');
            $table->string('author', 255)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('language', 5)->default('en');
            $table->enum('content_type', ['article', 'tutorial', 'news', 'research', 'announcement']);
            $table->string('image_url', 2048)->nullable();
            $table->boolean('is_processed')->default(false)->comment('Has been paraphrased?');
            $table->boolean('is_duplicate')->default(false);
            $table->unsignedBigInteger('duplicate_of')->nullable()->comment('Points to original if duplicate');
            $table->timestamps();

            $table->index('content_source_id');
            $table->index('published_at');
            $table->index('is_processed');
            $table->index('is_duplicate');
            $table->index('language');
            $table->index('content_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collected_content');
    }
};
