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
        // Add SEO fields to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('sort_order');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->json('seo_schema')->nullable()->after('meta_keywords');
        });

        // Add SEO fields to tags table
        Schema::table('tags', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('usage_count');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'seo_schema']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords']);
        });
    }
};
