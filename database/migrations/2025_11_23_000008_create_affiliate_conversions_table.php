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
        Schema::create('affiliate_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_link_id')->constrained('affiliate_links')->cascadeOnDelete();
            $table->foreignId('click_id')->constrained('affiliate_clicks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('conversion_type', ['signup', 'subscription', 'upgrade', 'purchase'])->index();
            $table->decimal('conversion_value', 10, 2)->comment('Base value of conversion');
            $table->unsignedInteger('commission_rate')->comment('Commission rate at time of conversion');
            $table->decimal('commission_amount', 10, 2)->index();
            $table->enum('status', ['pending', 'completed', 'refunded'])->default('pending')->index();
            $table->json('metadata')->nullable()->comment('Additional conversion data');
            $table->timestamps();

            $table->index(['affiliate_link_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_conversions');
    }
};
