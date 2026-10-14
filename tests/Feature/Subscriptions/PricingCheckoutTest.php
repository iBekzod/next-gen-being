<?php

namespace Tests\Feature\Subscriptions;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PricingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private const CHECKOUT_URL = 'https://nextgenbeing.lemonsqueezy.com/checkout/custom/abc?signature=x';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'lemon-squeezy.api_key' => 'test-key',
            'lemon-squeezy.store' => '194946',
            'lemon-squeezy.signing_secret' => 'test-secret',
            'services.lemonsqueezy.basic_variant_id' => '1043577',
            'services.lemonsqueezy.pro_variant_id' => '1043578',
            'services.lemonsqueezy.team_variant_id' => '1043579',
        ]);

        Http::fake([
            'api.lemonsqueezy.com/*' => Http::response(['data' => ['attributes' => ['url' => self::CHECKOUT_URL]]], 201),
        ]);
    }

    public function test_guest_goes_straight_to_the_lemon_squeezy_checkout_for_the_right_variant(): void
    {
        $this->get(route('subscription.checkout', 'pro'))->assertRedirect(self::CHECKOUT_URL);

        Http::assertSent(function ($request) {
            $attrs = $request['data']['attributes'];

            return $request['data']['relationships']['variant']['data']['id'] === '1043578'
                && $request['data']['relationships']['store']['data']['id'] === '194946'
                && ($attrs['checkout_data']['custom']['guest_checkout'] ?? null) === '1'
                && ! isset($attrs['checkout_data']['custom']['billable_id']);
        });
    }

    public function test_logged_in_user_checkout_carries_email_and_user_id(): void
    {
        $user = User::factory()->create(['email' => 'reader@example.com']);

        $this->actingAs($user)->get(route('subscription.checkout', 'basic'))->assertRedirect(self::CHECKOUT_URL);

        Http::assertSent(function ($request) use ($user) {
            $data = $request['data']['attributes']['checkout_data'];

            return $request['data']['relationships']['variant']['data']['id'] === '1043577'
                && $data['email'] === 'reader@example.com'
                && $data['custom']['user_id'] === (string) $user->id
                && $data['custom']['billable_id'] === (string) $user->id;
        });
    }

    public function test_unknown_plan_is_404_and_unconfigured_plan_returns_to_pricing(): void
    {
        $this->get(route('subscription.checkout', 'nope'))->assertNotFound();

        config(['services.lemonsqueezy.team_variant_id' => null]);
        $this->get(route('subscription.checkout', 'enterprise'))
            ->assertRedirect(route('subscription.plans'))
            ->assertSessionHas('error');
    }

    public function test_api_failure_returns_to_pricing_with_an_error(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['api.lemonsqueezy.com/*' => Http::response(['errors' => [['detail' => 'Your API key has expired.']]], 401)]);

        $this->get(route('subscription.checkout', 'basic'))
            ->assertRedirect(route('subscription.plans'))
            ->assertSessionHas('error');
    }

    public function test_pricing_buttons_link_to_checkout_for_guests_and_users(): void
    {
        $this->get(route('subscription.plans'))
            ->assertOk()
            ->assertSee(route('subscription.checkout', 'pro'), false)
            ->assertDontSee('redirect=', false);

        $this->actingAs(User::factory()->create())->get(route('subscription.plans'))
            ->assertOk()
            ->assertSee(route('subscription.checkout', 'pro'), false);
    }

    private function signedGuestWebhook(string $email, string $event = 'order_created', ?string $signature = null)
    {
        $body = json_encode([
            'meta' => ['event_name' => $event, 'custom_data' => ['guest_checkout' => '1', 'plan' => 'basic']],
            'data' => ['id' => '99', 'attributes' => [
                'customer_id' => 555, 'user_email' => $email, 'user_name' => 'Pat Guest',
                'identifier' => '8f7c2a3e-1b4d-4c5e-9a6b-0d1e2f3a4b5c', 'order_number' => 1, 'currency' => 'USD', 'subtotal' => 999,
                'discount_total' => 0, 'tax' => 0, 'total' => 999, 'tax_name' => '', 'status' => 'paid',
                'status_formatted' => 'Paid', 'refunded' => false, 'refunded_at' => null,
                'total_formatted' => '$9.99', 'created_at' => now()->toIso8601String(),
                'order_items' => [], 'product_id' => 1, 'variant_id' => 1043577,
                'first_order_item' => ['product_id' => 1, 'variant_id' => 1043577],
            ]],
        ]);

        return $this->call('POST', '/lemon-squeezy/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature ?? hash_hmac('sha256', $body, 'test-secret'),
        ], $body);
    }

    public function test_signed_guest_webhook_creates_and_links_an_account_by_email(): void
    {
        Notification::fake();
        $this->signedGuestWebhook('New.Guest@Example.com');

        $user = User::where('email', 'new.guest@example.com')->first();
        $this->assertNotNull($user, 'account should be created from the checkout email');
        $this->assertDatabaseHas('lemon_squeezy_customers', [
            'billable_id' => $user->id, 'billable_type' => $user->getMorphClass(),
        ]);
    }

    public function test_guest_webhook_links_to_an_existing_account_without_duplicating_it(): void
    {
        $existing = User::factory()->create(['email' => 'already@example.com']);

        $this->signedGuestWebhook('Already@example.com');

        $this->assertSame(1, User::where('email', 'already@example.com')->count());
        $this->assertDatabaseHas('lemon_squeezy_customers', ['billable_id' => $existing->id]);
    }

    public function test_forged_guest_webhook_never_creates_an_account(): void
    {
        $this->signedGuestWebhook('forged@example.com', 'order_created', 'deadbeef');

        $this->assertDatabaseMissing('users', ['email' => 'forged@example.com']);
    }
}
