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
        // Active readers - track who is currently viewing a post
        if (!Schema::hasTable('active_readers')) {
            Schema::create('active_readers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Anonymous users have null
            $table->string('session_id')->nullable(); // For tracking anonymous readers
            $table->string('ip_address')->nullable(); // IP address for geo-location
            $table->string('user_agent')->nullable(); // Browser info
            $table->timestamp('started_viewing_at');
            $table->timestamp('last_activity_at');
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->index('post_id');
            $table->index('user_id');
            $table->index('session_id');
            $table->index('last_activity_at'); // For cleanup queries
        });
        }

        // Reader locations - geographic data
        if (!Schema::hasTable('reader_locations')) {
            Schema::create('reader_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->string('ip_address')->unique(); // One location per IP per post
            $table->string('country_code', 2)->nullable(); // ISO 3166-1 alpha-2 code
            $table->string('country_name')->nullable();
            $table->string('state_province')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('timezone')->nullable();
            $table->string('isp')->nullable(); // Internet Service Provider
            $table->unsignedInteger('reader_count')->default(1); // Count of readers from this location
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->index('post_id');
            $table->index('country_code');
            $table->index('last_seen_at');
        });
        }

        // Reader analytics aggregated data
        if (!Schema::hasTable('reader_analytics')) {
            Schema::create('reader_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->unsignedInteger('total_readers_today')->default(0);
            $table->unsignedInteger('authenticated_readers_today')->default(0);
            $table->unsignedInteger('anonymous_readers_today')->default(0);
            $table->unsignedInteger('peak_concurrent_readers')->default(0);
            $table->time('peak_time')->nullable();
            $table->json('top_countries')->nullable(); // Top 5 countries
            $table->json('hourly_breakdown')->nullable(); // Reader count by hour
            $table->date('date');
            $table->timestamps();

            $table->unique(['post_id', 'date']); // One record per post per day
            $table->index('post_id');
            $table->index('date');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reader_analytics');
        Schema::dropIfExists('reader_locations');
        Schema::dropIfExists('active_readers');
    }
};
