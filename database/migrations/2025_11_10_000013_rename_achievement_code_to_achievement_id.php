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
        // Rename achievement_code to achievement_id in user_achievements table
        if (Schema::hasTable('user_achievements')) {
            Schema::table('user_achievements', function (Blueprint $table) {
                // Check if achievement_code exists (old schema) and achievement_id doesn't
                if (Schema::hasColumn('user_achievements', 'achievement_code') &&
                    !Schema::hasColumn('user_achievements', 'achievement_id')) {
                    // Rename the column
                    $table->renameColumn('achievement_code', 'achievement_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_achievements')) {
            Schema::table('user_achievements', function (Blueprint $table) {
                if (Schema::hasColumn('user_achievements', 'achievement_id') &&
                    !Schema::hasColumn('user_achievements', 'achievement_code')) {
                    $table->renameColumn('achievement_id', 'achievement_code');
                }
            });
        }
    }
};
