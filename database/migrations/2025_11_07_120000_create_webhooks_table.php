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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('url');
            $table->string('event_type')->index(); // post.published, post.commented, earning.created, etc.
            $table->json('events')->default('[]'); // array of events to trigger on
            $table->json('headers')->nullable(); // custom headers
            $table->enum('status', ['active', 'inactive', 'failed'])->default('active');
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('last_failed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->boolean('verify_ssl')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'event_type']);
            $table->index(['status', 'last_triggered_at']);
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained('webhooks')->onDelete('cascade');
            $table->string('event_type');
            $table->integer('response_status')->nullable();
            $table->json('request_payload');
            $table->text('response_body')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['webhook_id', 'created_at']);
            $table->index(['event_type', 'success']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
    }
};
