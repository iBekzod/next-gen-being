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
        // Chastota tanlanmagan — standart HAFTALIK bo'lishi kerak, chunki
        // kunlik digest ko'p kunlar hech narsa yubormaydi.
        $this->assertSame('weekly', $subscription->frequency);
        $this->assertSame(
            $listing->slug,
            data_get($subscription->preferences, 'pending_prompt_listing')
        );
    }

    public function test_kunlikni_tanlagan_obunachi_kunlik_oladi(): void
    {
        Mail::fake();
        $listing = $this->listing();

        $this->post(route('marketplace.prompt.request', $listing), [
            'email' => 'kunlik@example.org',
            'frequency' => 'daily',
        ])->assertRedirect();

        $this->assertSame(
            'daily',
            NewsletterSubscription::where('email', 'kunlik@example.org')->firstOrFail()->frequency
        );
    }

    public function test_yaroqsiz_chastota_rad_etiladi(): void
    {
        Mail::fake();
        $listing = $this->listing();

        $this->post(route('marketplace.prompt.request', $listing), [
            'email' => 'yaroqsiz@example.org',
            'frequency' => 'hourly',
        ])->assertSessionHasErrors('frequency');

        $this->assertSame(0, NewsletterSubscription::count());
    }

    public function test_sahifada_chastota_tanlovi_bor(): void
    {
        $listing = $this->listing();

        $response = $this->get(route('marketplace.show', $listing));

        $response->assertOk();
        $response->assertSee('name="frequency"', false);
        $response->assertSee('value="weekly"', false);
        $response->assertSee('value="daily"', false);
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

        // Formadan "kunlik" kelsa ham mavjud obunachining tanloviga tegilmaydi.
        $this->post(route('marketplace.prompt.request', $listing), [
            'email' => 'haftalik@example.org',
            'frequency' => 'daily',
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

    public function test_tasdiqlashdan_song_kutilayotgan_prompt_kaliti_ochirilaladi(): void
    {
        Mail::fake();
        $listing = $this->listing();

        $this->post(route('marketplace.prompt.request', $listing), ['email' => 'kalit@example.org']);

        $subscription = NewsletterSubscription::where('email', 'kalit@example.org')->firstOrFail();

        $this->get(route('newsletter.verify', $subscription->token));

        $this->assertArrayNotHasKey(
            'pending_prompt_listing',
            $subscription->fresh()->preferences ?? [],
            'pending_prompt_listing kaliti tasdiqlashdan keyin ham saqlanib qolyapti'
        );
    }

    public function test_ikkinchi_bosish_xatolik_sahifasini_koradi(): void
    {
        Mail::fake();
        $listing = $this->listing();

        $this->post(route('marketplace.prompt.request', $listing), ['email' => 'ikkinchi@example.org']);

        $subscription = NewsletterSubscription::where('email', 'ikkinchi@example.org')->firstOrFail();

        // Birinchi bosish — tasdiqlaydi va faylga yo'naltiradi.
        $this->get(route('newsletter.verify', $subscription->token));

        // Xuddi shu havolaga ikkinchi bosish — obuna allaqachon tasdiqlangan,
        // shuning uchun verify() endi null qaytaradi.
        $response = $this->get(route('newsletter.verify', $subscription->token));

        $response->assertOk();
        $response->assertDontSee('/prompt/');
        $this->assertNull($response->headers->get('Location'));
    }

    public function test_tasdiqlangan_obunachi_qayta_sorasa_yangi_havola_oladi(): void
    {
        Mail::fake();
        $listing = $this->listing();

        $subscription = NewsletterSubscription::create([
            'email' => 'qaytauchun@example.org',
            'token' => \Illuminate\Support\Str::random(40),
            'frequency' => 'daily',
            'is_active' => true,
            'verified_at' => now(),
        ]);

        $response = $this->post(route('marketplace.prompt.request', $listing), [
            'email' => $subscription->email,
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/prompt/', (string) $response->headers->get('Location'));
    }

    public function test_sahifada_prompt_uchun_email_formasi_bor(): void
    {
        $listing = $this->listing();

        $response = $this->get(route('marketplace.show', $listing));

        $response->assertOk();
        $response->assertSee(route('marketplace.prompt.request', $listing), false);
        $response->assertSee('name="email"', false);
    }

    public function test_arxivlangan_code_tier_sahifada_korinmaydi(): void
    {
        $listing = $this->listing();

        $response = $this->get(route('marketplace.show', $listing));

        $response->assertOk();
        $response->assertDontSee('>code<', false);
    }
}
