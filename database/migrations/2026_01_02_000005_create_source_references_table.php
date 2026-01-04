<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->unsignedBigInteger('collected_content_id')->nullable()->constrained('collected_content')->nullOnDelete();
            $table->string('title', 500);
            $table->string('url', 2048);
            $table->string('author', 255)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('accessed_at')->nullable();
            $table->string('domain', 255)->nullable();
            $table->enum('citation_style', ['apa', 'chicago', 'harvard', 'inline'])->default('inline');
            $table->integer('position_in_post')->nullable()->comment('Which reference # in the post');
            $table->timestamps();

            $table->index('post_id');
            $table->index(['post_id', 'position_in_post']);
            $table->index('domain');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_references');
    }
};
