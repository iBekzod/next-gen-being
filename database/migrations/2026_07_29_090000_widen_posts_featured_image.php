<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * featured_image was varchar(255), but image URLs (e.g. Unsplash links with
     * crop/ixid/ixlib query params) routinely exceed 255 characters, causing
     * SQLSTATE[22001] "value too long" on insert — which broke the blog-bot's
     * post submission (POST /api/bot/post) with a 500. Widen it to text.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->text('featured_image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('featured_image', 255)->nullable()->change();
        });
    }
};
