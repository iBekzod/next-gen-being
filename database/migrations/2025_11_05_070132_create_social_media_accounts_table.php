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
        Schema::create('social_media_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Platform details
            $table->string('platform', 50); // youtube, instagram, twitter, linkedin, tiktok, facebook, telegram
            $table->string('account_type', 20)->default('personal'); // 'personal' or 'platform_official'

            // OAuth tokens (encrypted at model level)
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            // Platform-specific identifiers
            $table->string('platform_user_id')->nullable();
            $table->string('platform_username')->nullable();

            // Account information
            $table->string('account_name')->nullable();
            $table->text('account_avatar')->nullable();
            $table->integer('follower_count')->default(0);

            // Publishing settings
            $table->boolean('auto_publish')->default(false);
            $table->jsonb('publish_schedule')->nullable(); // {"days": ["mon", "wed"], "times": ["09:00", "18:00"]}

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_published_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'platform']);
            $table->index(['platform', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_accounts');
    }
};
