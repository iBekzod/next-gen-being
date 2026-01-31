<?php

namespace App\Services;

use App\Models\DigitalProduct;
use App\Models\ProductPurchase;
use Illuminate\Support\Str;

class DigitalProductService
{
    public function __construct(
        protected BloggerMonetizationService $monetization
    ) {}

    public function createProduct(array $data): DigitalProduct
    {
        $product = DigitalProduct::create([
            ...$data,
            'slug' => Str::slug($data['title']),
            'status' => $data['status'] ?? 'draft',
        ]);

        return $product;
    }

    public function processPurchase(array $webhookData): ProductPurchase
    {
        $customData = $webhookData['meta']['custom_data'] ?? [];
        $productId = $customData['product_id'] ?? null;
        $userId = $customData['user_id'] ?? null;

        if (!$productId || !$userId) {
            throw new \Exception('Missing product_id or user_id in webhook data');
        }

        $product = DigitalProduct::findOrFail($productId);

        // Calculate revenue split
        $orderAmount = $webhookData['data']['attributes']['total'] / 100; // Convert from cents
        $creatorRevenue = $orderAmount * ($product->revenue_share_percentage / 100);
        $platformRevenue = $orderAmount - $creatorRevenue;

        $purchase = ProductPurchase::create([
            'user_id' => $userId,
            'digital_product_id' => $productId,
            'amount' => $orderAmount,
            'status' => 'completed',
            'lemonsqueezy_order_id' => $webhookData['data']['id'],
            'lemonsqueezy_receipt_url' => $webhookData['data']['attributes']['urls']['receipt'] ?? null,
            'license_key' => ProductPurchase::generateLicenseKey(),
            'creator_revenue' => $creatorRevenue,
            'platform_revenue' => $platformRevenue,
        ]);

        // Track creator earnings
        $this->monetization->recordDigitalProductSale(
            $product->creator,
            $product,
            $creatorRevenue
        );

        // Increment product stats
        $product->incrementPurchases();

        return $purchase;
    }
}
