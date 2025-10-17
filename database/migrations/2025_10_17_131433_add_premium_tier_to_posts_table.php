<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('premium_tier', 20)->nullable()->after('is_premium');
            $table->unsignedInteger('preview_percentage')->default(30)->after('premium_tier');
            $table->text('paywall_message')->nullable()->after('preview_percentage');

            $table->index('premium_tier');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['premium_tier', 'preview_percentage', 'paywall_message']);
        });
    }
};
