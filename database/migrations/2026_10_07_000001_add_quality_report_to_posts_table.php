<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editor-review result sent by blog-bot (quality_gate.py): rubric scores,
 * mean, passed. content:drip only auto-publishes drafts whose review passed
 * (PublishGate 'no_quality_review'), so structurally valid but generic
 * drafts no longer reach the public corpus on their own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('quality_report')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('quality_report');
        });
    }
};
