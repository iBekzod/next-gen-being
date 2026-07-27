<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_products', function (Blueprint $table) {
            $table->foreignId('listing_id')->nullable()->after('creator_id')
                ->constrained('marketplace_listings')->nullOnDelete();
            // Stored as a plain string (validated in the model) to avoid pgsql
            // enum-alter friction; values: prompt|design|code|bundle.
            $table->string('tier')->nullable()->after('type');
            $table->index(['listing_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::table('digital_products', function (Blueprint $table) {
            $table->dropIndex(['listing_id', 'tier']);
            $table->dropConstrainedForeignId('listing_id');
            $table->dropColumn('tier');
        });
    }
};
