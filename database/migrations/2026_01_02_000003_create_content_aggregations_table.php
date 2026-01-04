<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_aggregations', function (Blueprint $table) {
            $table->id();
            $table->string('topic', 500);
            $table->text('description')->nullable();
            $table->json('source_ids')->comment('Array of content_source IDs that contributed');
            $table->json('collected_content_ids')->comment('Array of collected_content IDs in this group');
            $table->unsignedBigInteger('primary_source_id')->nullable();
            $table->float('confidence_score')->default(0.75)->comment('0-1 how confident this grouping is');
            $table->timestamps();

            $table->index('topic');
            $table->index('primary_source_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_aggregations');
    }
};
