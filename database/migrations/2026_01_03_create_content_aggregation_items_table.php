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
        Schema::create('content_aggregation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_aggregation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collected_content_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->float('similarity_score')->default(0);
            $table->timestamps();

            $table->unique(['content_aggregation_id', 'collected_content_id']);
            $table->index('similarity_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_aggregation_items');
    }
};
