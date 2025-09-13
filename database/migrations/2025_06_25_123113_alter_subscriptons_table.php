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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add provider columns
            $table->string('provider')->default('paddle')->after('user_id'); // 'paddle' or 'lemonsqueezy'
            $table->string('provider_id')->after('provider'); // replaces lemonsqueezy_id

            // Add Paddle-specific fields
            $table->string('price_id')->nullable()->after('variant_id');
            $table->timestamp('current_period_start')->nullable()->after('trial_ends_at');
            $table->timestamp('current_period_end')->nullable()->after('current_period_start');

            // Add index for provider queries
            $table->index(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_id', 'price_id', 'current_period_start', 'current_period_end']);
            $table->dropIndex(['provider', 'provider_id']);
        });
    }
};
