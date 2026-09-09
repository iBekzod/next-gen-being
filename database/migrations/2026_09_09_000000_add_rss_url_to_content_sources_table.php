<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_sources', function (Blueprint $table) {
            $table->string('rss_url', 2048)
                ->nullable()
                ->after('url')
                ->comment('Explicit RSS/Atom feed URL; when null the scraper probes for one');
        });
    }

    public function down(): void
    {
        Schema::table('content_sources', function (Blueprint $table) {
            $table->dropColumn('rss_url');
        });
    }
};
