<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_premium_content')->default(false);
            $table->boolean('viewed_as_trial')->default(false);
            $table->boolean('converted_to_paid')->default(false);
            $table->unsignedInteger('time_on_page')->nullable();
            $table->unsignedInteger('scroll_depth')->nullable();
            $table->boolean('clicked_upgrade')->default(false);
            $table->string('referrer')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
            $table->index('post_id');
            $table->index('session_id');
            $table->index('viewed_at');
            $table->index('is_premium_content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};
