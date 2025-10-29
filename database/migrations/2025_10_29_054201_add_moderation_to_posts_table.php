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
            // Moderation status: pending, approved, rejected
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');

            // Moderator who reviewed
            $table->foreignId('moderated_by')->nullable()->after('moderation_status')->constrained('users')->nullOnDelete();

            // When moderated
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');

            // Moderation notes (reason for rejection, feedback, etc.)
            $table->text('moderation_notes')->nullable()->after('moderated_at');

            // AI pre-check results (quality score, content flags)
            $table->json('ai_moderation_check')->nullable()->after('moderation_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['moderated_by']);
            $table->dropColumn([
                'moderation_status',
                'moderated_by',
                'moderated_at',
                'moderation_notes',
                'ai_moderation_check'
            ]);
        });
    }
};
