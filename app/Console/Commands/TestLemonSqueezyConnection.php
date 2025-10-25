<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\LemonSqueezyService;

class TestLemonSqueezyConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lemonsqueezy:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test LemonSqueezy API connection and configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing LemonSqueezy Configuration...');
        $this->newLine();

        // Check configuration
        $this->info('📋 Configuration Check:');
        $apiKey = config('lemon-squeezy.api_key');
        $storeId = config('lemon-squeezy.store');
        $testMode = config('services.lemonsqueezy.test_mode', false);

        $this->line('  API Key: ' . ($apiKey ? '✅ Set (' . substr($apiKey, 0, 20) . '...)' : '❌ Not set'));
        $this->line('  Store ID: ' . ($storeId ? "✅ {$storeId}" : '❌ Not set'));
        $this->line('  Test Mode: ' . ($testMode ? '✅ Enabled' : '⚠️  Disabled (Production)'));

        $basicVariant = config('services.lemonsqueezy.basic_variant_id');
        $proVariant = config('services.lemonsqueezy.pro_variant_id');
        $teamVariant = config('services.lemonsqueezy.team_variant_id');

        $this->line('  Basic Variant: ' . ($basicVariant ?: '❌ Not set'));
        $this->line('  Pro Variant: ' . ($proVariant ?: '❌ Not set'));
        $this->line('  Team Variant: ' . ($teamVariant ?: '❌ Not set'));

        if (!$apiKey || !$storeId) {
            $this->error('❌ Configuration incomplete. Please set LEMON_SQUEEZY_API_KEY and LEMON_SQUEEZY_STORE in .env');
            return 1;
        }

        $this->newLine();

        // Test API Connection
        $this->info('🔌 Testing API Connection...');

        try {
            $response = Http::withToken($apiKey)
                ->withHeaders([
                    'Accept' => 'application/vnd.api+json',
                    'Content-Type' => 'application/vnd.api+json',
                ])
                ->get('https://api.lemonsqueezy.com/v1/stores/' . $storeId);

            if ($response->successful()) {
                $store = $response->json('data');
                $this->line('  ✅ API Connection successful');
                $this->line('  Store Name: ' . ($store['attributes']['name'] ?? 'N/A'));
                $this->line('  Store Domain: ' . ($store['attributes']['domain'] ?? 'N/A'));
                $this->line('  Store URL: ' . ($store['attributes']['url'] ?? 'N/A'));
            } else {
                $this->error('  ❌ API Connection failed');
                $this->line('  Status: ' . $response->status());
                $this->line('  Response: ' . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('  ❌ Exception: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();

        // Test Variants
        $this->info('🎯 Testing Product Variants...');

        $variants = [
            'Basic' => $basicVariant,
            'Pro' => $proVariant,
            'Team' => $teamVariant,
        ];

        foreach ($variants as $name => $variantId) {
            if (!$variantId) {
                $this->line("  ⚠️  {$name}: Not configured");
                continue;
            }

            try {
                $response = Http::withToken($apiKey)
                    ->withHeaders([
                        'Accept' => 'application/vnd.api+json',
                        'Content-Type' => 'application/vnd.api+json',
                    ])
                    ->get('https://api.lemonsqueezy.com/v1/variants/' . $variantId);

                if ($response->successful()) {
                    $variant = $response->json('data');
                    $price = $variant['attributes']['price'] ?? 0;
                    $formattedPrice = '$' . number_format($price / 100, 2);
                    $this->line("  ✅ {$name}: {$formattedPrice} - " . ($variant['attributes']['name'] ?? 'N/A'));
                } else {
                    $this->line("  ❌ {$name} (ID: {$variantId}): Failed to fetch");
                }
            } catch (\Exception $e) {
                $this->line("  ❌ {$name}: " . $e->getMessage());
            }
        }

        $this->newLine();

        // Test Service Methods
        $this->info('⚙️  Testing LemonSqueezyService...');

        try {
            $service = new LemonSqueezyService();
            $this->line('  ✅ Service instantiated successfully');

            // Try to fetch a test customer (will return null if not found, but shouldn't throw error)
            $customer = $service->getCustomerByEmail('test@example.com');
            $this->line('  ✅ getCustomerByEmail() method works (returned: ' . ($customer ? 'found' : 'not found') . ')');
        } catch (\Exception $e) {
            $this->error('  ❌ Service error: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('✅ All tests passed! LemonSqueezy is configured correctly.');

        return 0;
    }
}
