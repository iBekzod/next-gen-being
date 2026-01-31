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
        Schema::create('product_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('digital_product_id')->constrained('digital_products')->cascadeOnDelete();

            // Payment
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'completed', 'refunded', 'failed'])->default('pending');

            // LemonSqueezy
            $table->string('lemonsqueezy_order_id')->nullable();
            $table->string('lemonsqueezy_receipt_url')->nullable();

            // License
            $table->string('license_key')->unique();
            $table->integer('download_count')->default(0);
            $table->integer('download_limit')->default(10); // Max 10 downloads
            $table->timestamp('expires_at')->nullable(); // For time-limited access

            // Revenue Share
            $table->decimal('creator_revenue', 10, 2); // 70% to creator
            $table->decimal('platform_revenue', 10, 2); // 30% to platform
            $table->boolean('creator_paid')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'digital_product_id']);
            $table->index('status');
            $table->index('license_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_purchases');
    }
};
