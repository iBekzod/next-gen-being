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
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'meta_title')) {
                    $table->string('meta_title')->nullable()->after('sort_order');
                }
                if (!Schema::hasColumn('categories', 'meta_description')) {
                    $table->text('meta_description')->nullable()->after('meta_title');
                }
                if (!Schema::hasColumn('categories', 'meta_keywords')) {
                    $table->string('meta_keywords')->nullable()->after('meta_description');
                }
                if (!Schema::hasColumn('categories', 'seo_schema')) {
                    $table->json('seo_schema')->nullable()->after('meta_keywords');
                }
            });
        }

        // Add SEO fields to tags table
        if (Schema::hasTable('tags')) {
            Schema::table('tags', function (Blueprint $table) {
                if (!Schema::hasColumn('tags', 'meta_title')) {
                    $table->string('meta_title')->nullable()->after('usage_count');
                }
                if (!Schema::hasColumn('tags', 'meta_description')) {
                    $table->text('meta_description')->nullable()->after('meta_title');
                }
                if (!Schema::hasColumn('tags', 'meta_keywords')) {
                    $table->string('meta_keywords')->nullable()->after('meta_description');
                }
            });
        }
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
