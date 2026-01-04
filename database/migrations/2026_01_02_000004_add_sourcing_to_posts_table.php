<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Track if this is a curated post
            $table->boolean('is_curated')->default(false)->after('is_premium');
            $table->enum('content_source_type', ['original', 'curated', 'aggregated'])->default('original')->after('is_curated');

            // Source tracking
            $table->unsignedBigInteger('content_aggregation_id')->nullable()->after('content_source_type');
            $table->json('source_ids')->nullable()->comment('Array of content_source IDs');
            $table->json('references')->nullable()->comment('Array of {title, url, author, domain, date}');

            // Language tracking for translations
            $table->string('base_language', 5)->default('en')->after('references');
            $table->unsignedBigInteger('base_post_id')->nullable()->after('base_language')->comment('Points to original language version');

            // Content processing metadata
            $table->float('paraphrase_confidence_score')->nullable()->after('base_post_id')->comment('0-1 confidence in paraphrasing');
            $table->boolean('is_fact_verified')->default(false)->after('paraphrase_confidence_score');
            $table->text('verification_notes')->nullable()->after('is_fact_verified');

            // Indices
            $table->index('is_curated');
            $table->index('content_source_type');
            $table->index('content_aggregation_id');
            $table->index('base_language');
            $table->index('base_post_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'is_curated',
                'content_source_type',
                'content_aggregation_id',
                'source_ids',
                'references',
                'base_language',
                'base_post_id',
                'paraphrase_confidence_score',
                'is_fact_verified',
                'verification_notes'
            ]);
        });
    }
};
