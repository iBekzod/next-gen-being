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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('series_title')->nullable()->after('category_id');
            $table->string('series_slug')->nullable()->after('series_title');
            $table->integer('series_part')->nullable()->after('series_slug');
            $table->integer('series_total_parts')->nullable()->after('series_part');
            $table->text('series_description')->nullable()->after('series_total_parts');

            $table->index('series_slug');
            $table->index(['series_slug', 'series_part']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['posts_series_slug_index']);
            $table->dropIndex(['posts_series_slug_series_part_index']);

            $table->dropColumn([
                'series_title',
                'series_slug',
                'series_part',
                'series_total_parts',
                'series_description'
            ]);
        });
    }
};
