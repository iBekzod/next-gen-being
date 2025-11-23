<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('post_id')->nullable()->constrained('posts')->onDelete('cascade');

            $table->decimal('amount', 8, 2);
            $table->string('currency')->default('USD');
            $table->text('message')->nullable();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->string('status')->default('pending'); // pending, completed, failed, refunded
            $table->boolean('is_anonymous')->default(false);

            $table->timestamps();

            $table->index('from_user_id');
            $table->index('to_user_id');
            $table->index('post_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tips');
    }
};
