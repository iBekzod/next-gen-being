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
        Schema::create('social_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 50)->index(); // twitter, linkedin, facebook, whatsapp, telegram, email
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('shared_at')->useCurrent();
            $table->timestamps();

            $table->index('shared_at');
            $table->index(['post_id', 'platform']);
        });

        // Add share count columns to posts table
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedInteger('total_shares')->default(0)->after('reading_time');
            $table->unsignedInteger('twitter_shares')->default(0)->after('total_shares');
            $table->unsignedInteger('linkedin_shares')->default(0)->after('twitter_shares');
            $table->unsignedInteger('facebook_shares')->default(0)->after('linkedin_shares');
            $table->unsignedInteger('whatsapp_shares')->default(0)->after('facebook_shares');
            $table->unsignedInteger('telegram_shares')->default(0)->after('whatsapp_shares');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'total_shares',
                'twitter_shares',
                'linkedin_shares',
                'facebook_shares',
                'whatsapp_shares',
                'telegram_shares',
            ]);
        });

        Schema::dropIfExists('social_shares');
    }
};
