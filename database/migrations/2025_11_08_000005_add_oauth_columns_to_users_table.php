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
        Schema::table('users', function (Blueprint $table) {
            // Add OAuth provider columns if they don't already exist
            $needsIndex = false;

            if (!Schema::hasColumn('users', 'oauth_provider')) {
                $table->string('oauth_provider')->nullable()->after('password');
                $needsIndex = true;
            }
            if (!Schema::hasColumn('users', 'oauth_provider_id')) {
                $table->string('oauth_provider_id')->nullable()->after('oauth_provider');
                $needsIndex = true;
            }
            if (!Schema::hasColumn('users', 'password_updated_at')) {
                $table->timestamp('password_updated_at')->nullable()->after('oauth_provider_id');
            }

            // Add indexes for faster lookups (only if we just added the oauth columns)
            if ($needsIndex) {
                $table->index(['oauth_provider', 'oauth_provider_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['oauth_provider', 'oauth_provider_id']);
            $table->dropColumn(['oauth_provider', 'oauth_provider_id', 'password_updated_at']);
        });
    }
};
