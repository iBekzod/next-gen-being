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
        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->unique();
            $table->string('token')->unique(); // For unsubscribe
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->json('preferences')->nullable(); // Topics, categories interest
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'frequency']);
            $table->index('email');
        });

        // Newsletter campaigns tracking
        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('content');
            $table->enum('type', ['digest', 'announcement', 'premium_teaser', 'personalized'])->default('digest');
            $table->enum('status', ['draft', 'scheduled', 'sent'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });

        // Email engagement tracking
        Schema::create('newsletter_engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('newsletter_campaigns')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('newsletter_subscriptions')->cascadeOnDelete();
            $table->boolean('opened')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->boolean('clicked')->default(false);
            $table->timestamp('clicked_at')->nullable();
            $table->string('clicked_url')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'subscription_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_engagements');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_subscriptions');
    }
};
