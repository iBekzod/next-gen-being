<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paywall_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->string('interaction_type', 50);
            $table->string('paywall_type', 50);
            $table->boolean('converted')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('interacted_at')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
            $table->index('post_id');
            $table->index('interaction_type');
            $table->index('converted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paywall_interactions');
    }
};
