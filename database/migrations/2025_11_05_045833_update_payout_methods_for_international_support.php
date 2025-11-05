<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL doesn't support ENUM modification directly
        // We need to: 1) Drop constraint, 2) Alter column, 3) Add new constraint

        DB::statement("
            ALTER TABLE payout_requests
            DROP CONSTRAINT IF EXISTS payout_requests_payout_method_check
        ");

        DB::statement("
            ALTER TABLE payout_requests
            ALTER COLUMN payout_method TYPE VARCHAR(50)
        ");

        DB::statement("
            ALTER TABLE payout_requests
            ADD CONSTRAINT payout_requests_payout_method_check
            CHECK (payout_method IN ('wise', 'payoneer', 'stripe', 'bank_wire', 'crypto_usdt', 'crypto_usdc', 'paypal', 'bank_transfer'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE payout_requests
            DROP CONSTRAINT IF EXISTS payout_requests_payout_method_check
        ");

        DB::statement("
            ALTER TABLE payout_requests
            ADD CONSTRAINT payout_requests_payout_method_check
            CHECK (payout_method IN ('bank_transfer', 'paypal', 'stripe'))
        ");
    }
};
