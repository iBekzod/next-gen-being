<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per click on a /go/<slug> outbound (affiliate) link.
 *
 * Deliberately separate from affiliate_clicks: that table belongs to the
 * creator-referral feature (foreign key to affiliate_links per user), while
 * this one tracks the site's own config-driven partner links.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->index();
            $table->unsignedBigInteger('post_id')->nullable()->index();
            $table->boolean('is_affiliate')->default(false);
            $table->string('referrer', 500)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('lang', 8)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_clicks');
    }
};
