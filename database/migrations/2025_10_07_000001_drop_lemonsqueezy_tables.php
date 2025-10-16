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
        // Drop old subscription tables from previous payment provider
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
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
