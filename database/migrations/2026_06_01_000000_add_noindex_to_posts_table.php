<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('noindex')->default(false)->after('status');
            $table->string('noindex_reason')->nullable()->after('noindex');
            $table->index('noindex');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['noindex']);
            $table->dropColumn(['noindex', 'noindex_reason']);
        });
    }
};
