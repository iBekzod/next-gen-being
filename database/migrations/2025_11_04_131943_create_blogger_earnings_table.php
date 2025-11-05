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
        Schema::create('blogger_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Earning details
            $table->enum('type', ['follower_milestone', 'premium_content', 'engagement_bonus', 'manual_adjustment']);
            $table->decimal('amount', 10, 2); // Amount in USD
            $table->string('currency', 3)->default('USD');

            // Milestone tracking (for follower milestones)
            $table->integer('milestone_value')->nullable(); // e.g., 100, 500, 1000 followers
            $table->json('metadata')->nullable(); // Additional data (post_id for premium, etc.)

            // Payout tracking
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payout_method')->nullable(); // stripe, paypal, etc.
            $table->string('payout_reference')->nullable(); // transaction ID

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogger_earnings');
    }
};
