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
        // Drop Paddle Cashier tables
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('customers');

        // Drop Paddle customer columns from users table if they exist
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'paddle_id')) {
                    $table->dropColumn('paddle_id');
                }
                if (Schema::hasColumn('users', 'paddle_email')) {
                    $table->dropColumn('paddle_email');
                }
                if (Schema::hasColumn('users', 'paddle_trial_ends_at')) {
                    $table->dropColumn('paddle_trial_ends_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to recreate Paddle tables
    }
};
