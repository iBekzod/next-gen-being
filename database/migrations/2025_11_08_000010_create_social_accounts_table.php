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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('provider'); // google, github, discord, etc.
            $table->string('provider_id'); // The user's ID from the provider
            $table->string('provider_email')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->longText('access_token');
            $table->longText('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable(); // Store additional provider data
            $table->timestamps();

            // Unique index for provider + provider_id combination
            $table->unique(['provider', 'provider_id']);
            $table->index('user_id');
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
