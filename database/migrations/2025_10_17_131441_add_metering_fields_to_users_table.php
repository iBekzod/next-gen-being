<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('free_articles_used')->default(0)->after('email_verified_at');
            $table->timestamp('free_articles_reset_at')->nullable()->after('free_articles_used');
            $table->timestamp('last_upgrade_prompt_at')->nullable()->after('free_articles_reset_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['free_articles_used', 'free_articles_reset_at', 'last_upgrade_prompt_at']);
        });
    }
};
