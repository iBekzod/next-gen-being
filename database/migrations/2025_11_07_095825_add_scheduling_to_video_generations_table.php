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
        Schema::table('video_generations', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('status');
            $table->boolean('auto_publish')->default(false)->after('scheduled_at');
            $table->jsonb('publish_platforms')->nullable()->after('auto_publish'); // ["youtube", "tiktok", "instagram"]
            $table->string('priority', 20)->default('normal')->after('publish_platforms'); // low, normal, high, urgent
            $table->integer('retry_count')->default(0)->after('error_message');
            $table->timestamp('last_retry_at')->nullable()->after('retry_count');

            // Add indexes for efficient scheduling queries
            $table->index(['status', 'scheduled_at']);
            $table->index(['priority', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_generations', function (Blueprint $table) {
            $table->dropIndex(['status', 'scheduled_at']);
            $table->dropIndex(['priority', 'scheduled_at']);

            $table->dropColumn([
                'scheduled_at',
                'auto_publish',
                'publish_platforms',
                'priority',
                'retry_count',
                'last_retry_at'
            ]);
        });
    }
};
