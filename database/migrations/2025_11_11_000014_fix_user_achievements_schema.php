<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only proceed if user_achievements table exists
        if (!Schema::hasTable('user_achievements')) {
            return;
        }

        // Check if we need to fix the schema
        $columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name='user_achievements'");

        $hasWrongType = false;
        foreach ($columns as $col) {
            // Check if achievement_id is VARCHAR instead of BIGINT
            if ($col->column_name === 'achievement_id' && strpos($col->data_type, 'character varying') !== false) {
                $hasWrongType = true;
                break;
            }
        }

        if (!$hasWrongType) {
            return; // Schema is already correct
        }

        // Drop the description column if it exists (shouldn't be in pivot)
        if (Schema::hasColumn('user_achievements', 'description')) {
            Schema::table('user_achievements', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }

        // Convert achievement_id from VARCHAR to BIGINT
        DB::statement('ALTER TABLE user_achievements ALTER COLUMN achievement_id TYPE bigint USING achievement_id::bigint');

        // Add proper foreign key constraint
        Schema::table('user_achievements', function (Blueprint $table) {
            $table->foreign('achievement_id')->references('id')->on('achievements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // This migration fixes schema issues, reverting would cause problems
        // Cannot safely reverse schema changes
    }
};
