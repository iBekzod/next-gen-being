<?php

namespace Tests\Feature\Marketplace;

use App\Models\MarketplaceListing;
use App\Models\NewsletterSubscription;
use App\Models\ProductPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PromptGateTest extends TestCase
{
    use RefreshDatabase;

    private function listing(): MarketplaceListing
    {
        User::factory()->create();
        $this->artisan('marketplace:sync-listings');

        return MarketplaceListing::where('slug', 'fittrack-workout-saas')->firstOrFail();
    }

    public function test_email_yuborish_obuna_yaratadi(): void
    {
        Mail::fake();
        $listing = $this->listing();

        $this->post(route('marketplace.prompt.request', $listing), [
            'email' => 'yangi@example.org',
        ])->assertRedirect();

        $subscription = NewsletterSubscription::where('email', 'yangi@example.org')->first();

        $this->assertNotNull($subscription, 'obuna yaratilmadi');
        $this->assertSame('daily', $subscription->frequency);
        $this->assertSame(
            $listing->slug,
            data_get($subscription->preferences, 'pending_prompt_listing')
        );
    }

    public function test_bepul_prompt_uchun_sotuv_yozuvi_yaratilmaydi(): void
    {
        Mail::fake();
        $listing = $this->listing();

        $this->post(route('marketplace.prompt.request', $listing), [
            'email' => 'yangi@example.org',
        ]);

        $this->assertSame(0, ProductPurchase::count(), 'email yig\'ish sotuv sifatida yozildi');
    }

    public function test_mavjud_obunachining_chastotasi_ozgarmaydi(): void
    {
        Mail::fake();
        $listing = $this->listing();

        $haftalik = app(\App\Services\NewsletterService::class)
            ->subscribe('haftalik@example.org', null, 'weekly', ['topics' => ['devops']]);

        $this->post(route('marketplace.prompt.request', $listing), [
            'email' => 'haftalik@example.org',
        ]);

        $yangilangan = $haftalik->fresh();

        // Chastota o'zgarmasligi SHART — foydalanuvchi buni so'ramagan.
        $this->assertSame('weekly', $yangilangan->frequency);
        // Eski sozlamalar ham saqlanishi kerak.
        $this->assertSame(['devops'], data_get($yangilangan->preferences, 'topics'));
        $this->assertSame($listing->slug, data_get($yangilangan->preferences, 'pending_prompt_listing'));
    }

    public function test_yaroqsiz_email_rad_etiladi(): void
    {
        $listing = $this->listing();

        $this->post(route('marketplace.prompt.request', $listing), [
            'email' => 'email-emas',
        ])->assertSessionHasErrors('email');

        $this->assertSame(0, NewsletterSubscription::count());
    }

    public function test_imzolangan_havola_faylni_beradi(): void
    {
        $listing = $this->listing();
        $subscription = NewsletterSubscription::create([
            'email' => 'tasdiqlangan@example.org',
            'token' => \Illuminate\Support\Str::random(40),
            'frequency' => 'daily',
            'is_active' => true,
            'verified_at' => now(),
        ]);

        $url = app(\App\Services\NewsletterService::class)->promptDownloadUrl($subscription, $listing);

        $response = $this->get($url);

        $response->assertOk();
        // Storage::download() infers the content-type from the file extension via
        // Symfony's MIME guesser. For a .md deliverable that resolves to
        // 'text/markdown', not the generic 'application/octet-stream' the brief
        // assumed — verified by running this test and reading the actual header.
        $this->assertSame(
            'text/markdown',
            explode(';', (string) $response->headers->get('content-type'))[0]
        );
    }

    public function test_imzosiz_havola_rad_etiladi(): void
    {
        $listing = $this->listing();
        $subscription = NewsletterSubscription::create([
            'email' => 'tasdiqlangan@example.org',
            'token' => \Illuminate\Support\Str::random(40),
            'frequency' => 'daily',
            'is_active' => true,
            'verified_at' => now(),
        ]);

        $this->get(route('marketplace.prompt.download', [
            'listing' => $listing->slug,
            'subscription' => $subscription->id,
        ]))->assertForbidden();
    }

    public function test_buzilgan_imzo_rad_etiladi(): void
    {
        $listing = $this->listing();
        $subscription = NewsletterSubscription::create([
            'email' => 'tasdiqlangan@example.org',
            'token' => \Illuminate\Support\Str::random(40),
            'frequency' => 'daily',
            'is_active' => true,
            'verified_at' => now(),
        ]);

        $url = app(\App\Services\NewsletterService::class)->promptDownloadUrl($subscription, $listing);

        $this->get($url . 'buzildi')->assertForbidden();
    }

    public function test_tasdiqlash_promptga_yonaltiradi(): void
    {
        Mail::fake();
        $listing = $this->listing();

        $this->post(route('marketplace.prompt.request', $listing), ['email' => 'yangi@example.org']);

        $subscription = NewsletterSubscription::where('email', 'yangi@example.org')->firstOrFail();

        $response = $this->get(route('newsletter.verify', $subscription->token));

        $response->assertRedirect();
        $this->assertStringContainsString('/prompt/', (string) $response->headers->get('Location'));
        $this->assertNotNull($subscription->fresh()->verified_at);
    }

    public function test_promptsiz_obuna_odatdagi_sahifani_koradi(): void
    {
        Mail::fake();
        $subscription = app(\App\Services\NewsletterService::class)
            ->subscribe('oddiy@example.org', null, 'weekly');

        $this->get(route('newsletter.verify', $subscription->token))
            ->assertOk();
    }
}
