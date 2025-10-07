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
        // Drop old LemonSqueezy tables to make way for Paddle Cashier tables
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        // Note: customer_columns was added by old migration,
        // Paddle will create its own customers table
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse - old LemonSqueezy structure is gone
        // This is a one-way migration
    }
};
