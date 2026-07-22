<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            // Automated welcome/onboarding sequence progress.
            // welcome_step = index of the last onboarding email delivered (0 = none).
            $table->unsignedTinyInteger('welcome_step')->default(0)->after('last_sent_at');
            $table->timestamp('welcome_sent_at')->nullable()->after('welcome_step');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['welcome_step', 'welcome_sent_at']);
        });
    }
};
