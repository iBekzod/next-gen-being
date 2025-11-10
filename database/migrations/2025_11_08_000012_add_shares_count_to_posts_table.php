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
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                // Add shares_count if it doesn't exist
                if (!Schema::hasColumn('posts', 'shares_count')) {
                    $table->unsignedInteger('shares_count')->default(0)->after('likes_count');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                if (Schema::hasColumn('posts', 'shares_count')) {
                    $table->dropColumn('shares_count');
                }
            });
        }
    }
};
