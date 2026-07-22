<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The blogger_earnings.type enum was created before digital products, tips, and
 * affiliates existed, so inserting 'digital_product_sale', 'tip', or
 * 'affiliate_commission' violated the check constraint and rolled back the
 * whole purchase/tip/commission. Extend the allowed values to match the code.
 */
return new class extends Migration
{
    private array $all = [
        'follower_milestone', 'premium_content', 'engagement_bonus', 'manual_adjustment',
        'digital_product_sale', 'affiliate_commission', 'tip',
    ];

    private array $original = [
        'follower_milestone', 'premium_content', 'engagement_bonus', 'manual_adjustment',
    ];

    public function up(): void
    {
        $this->setAllowed($this->all);
    }

    public function down(): void
    {
        $this->setAllowed($this->original);
    }

    private function setAllowed(array $values): void
    {
        $list = "'" . implode("','", $values) . "'";
        DB::statement('ALTER TABLE blogger_earnings DROP CONSTRAINT IF EXISTS blogger_earnings_type_check');
        DB::statement("ALTER TABLE blogger_earnings ADD CONSTRAINT blogger_earnings_type_check CHECK (type::text = ANY (ARRAY[$list]::text[]))");
    }
};
