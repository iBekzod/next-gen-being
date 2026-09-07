# Bepul promptlar va email yig'ish (C3) — Amalga oshirish rejasi

> **Agentik ishchilar uchun:** MAJBURIY SUB-SKILL: bu rejani vazifama-vazifa bajarish uchun superpowers:subagent-driven-development (tavsiya etiladi) yoki superpowers:executing-plans skill'idan foydalaning. Qadamlar checkbox (`- [ ]`) sintaksisida kuzatiladi.

**Maqsad:** Marketplace'ning eng kuchli aktivini — jonli demo bilan isbotlangan `-prompt.md` qurilish rejasini — pullik SKU'dan **bepul, email evaziga beriladigan magnitga** aylantirish, va shu bilan email ro'yxatini o'stira boshlash.

**Arxitektura:** Yangi `PromptGateController` mehmondan faqat email so'raydi (ro'yxatdan o'tish YO'Q). Email mavjud `NewsletterService::subscribe()` ga uzatiladi, so'ralgan listing esa obunaning `preferences` JSON'iga yoziladi. Mavjud double-opt-in tasdiqlash havolasi bosilganda, `NewsletterController::verify()` foydalanuvchini **vaqtinchalik imzolangan** yuklab olish havolasiga yo'naltiradi. Ya'ni bitta harakat ham obunani tasdiqlaydi, ham faylni yetkazadi — bitta ishqalanish nuqtasi. `prompt` tier `is_free` bo'ladi, `code` tier esa nashrdan olinadi; `design` va `bundle` pullik qoladi va mavjud Lemon Squeezy quvuri **umuman o'zgarmaydi**.

**Texnologiyalar:** Laravel 12, PHP 8.4, PostgreSQL, PHPUnit 11, Blade, `Illuminate\Support\Facades\URL` (imzolangan marshrutlar), `Storage` `private` diski.

**Spec:** `docs/superpowers/specs/2026-09-02-marketplace-audience-engine-design.md` — §7 (C3), qarorlar D1 va D2.

**Ishchi katalog:** barcha yo'llar `d:\projects\MyProjects\nextgenbeing\next-gen-being` ga nisbatan.

**Test buyrug'i:** `php artisan test --filter=<TestClassOrMethod>`. To'liq to'plam: `php artisan test`.

## Boshlashdan oldingi shart

```bash
docker compose up -d ngb-database
php artisan test
```

Hozirgi holat: **71 ta test yashil**. Shu sondan pastga tushmasin.

## Global cheklovlar

- **Migratsiya YO'Q.** `digital_products` o'zgarishlari — ma'lumot, sxema emas. So'ralgan listing obunaning mavjud `preferences` JSON ustuniga yoziladi.
- **Mavjud checkout quvuriga tegilmaydi.** `ProductPurchase`, `LemonSqueezyService`, webhook, `download` marshruti — hammasi o'z holicha qoladi. Keyinchalik narxni qaytarish ma'lumot o'zgarishi bo'lishi kerak.
- **Bepul prompt yo'li Lemon Squeezy'ga umuman bog'liq bo'lmasin.**
- **Bepul prompt uchun `ProductPurchase` yozuvi YARATILMAYDI.** Email yig'ish — sotuv emas; ikkalasini aralashtirish `sales_count` ni buzadi.
- Rate limiting mavjud `newsletter.quick-subscribe` bilan bir xil: `throttle:5,1`.
- `.env` o'zgartirilmaydi. `BLOG_AUTO_PUBLISH` ga tegilmaydi.
- Kommit xabarlari ingliz tilida, Conventional Commits uslubida, oxirida:
  `Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>`

---

## Fayl tuzilmasi

**Yaratiladi:**
- `app/Console/Commands/SyncMarketplaceListings.php` — barcha listing seeder'larini bitta idempotent buyruqda yig'adi.
- `app/Http/Controllers/PromptGateController.php` — email qabul qilish + imzolangan yuklab olish.
- `tests/Feature/Marketplace/SyncListingsCommandTest.php`
- `tests/Feature/Marketplace/PromptGateTest.php`
- `tests/Feature/Newsletter/DailyDigestTest.php`

**O'zgartiriladi:**
- `database/seeders/Concerns/SeedsMarketplaceListing.php` — `prompt` bepul, `code` nashrdan olinadi.
- `routes/web.php` — prompt-gate marshrutlari.
- `app/Http/Controllers/NewsletterController.php` — tasdiqlashdan keyin yuklab olishga yo'naltirish.
- `resources/views/marketplace/show.blade.php` — prompt tier'da "Sotib olish" o'rniga email formasi.
- `app/Services/NewsletterService.php` — `generateDailyDigest()`.
- `app/Console/Commands/SendDailyNewsletter.php` (yaratiladi) va `routes/console.php`.
- `deploy.sh` — `marketplace:sync-listings` chaqiruvi.

---

## Vazifa 1: `marketplace:sync-listings` — takrorlanadigan nashr yo'li

Hozir har bir mahsulot production'ga qo'lda `php artisan db:seed --class=...` bilan chiqadi. Haftalik chiqarish qo'lda qadamga asoslansa, albatta o'tkazib yuboriladi.

**Fayllar:**
- Yaratish: `app/Console/Commands/SyncMarketplaceListings.php`
- Test: `tests/Feature/Marketplace/SyncListingsCommandTest.php`
- O'zgartirish: `deploy.sh`

**Interfeyslar:**
- Beradi: `marketplace:sync-listings` buyrug'i. Vazifa 2 uning orqali tier o'zgarishlarini tarqatadi.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Feature/Marketplace/SyncListingsCommandTest.php`:

```php
<?php

namespace Tests\Feature\Marketplace;

use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncListingsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyruq_barcha_listinglarni_yaratadi(): void
    {
        User::factory()->create();
        MarketplaceListing::query()->delete();

        $this->artisan('marketplace:sync-listings')->assertSuccessful();

        $slugs = MarketplaceListing::pluck('slug');

        foreach (['fittrack-workout-saas', 'linkfolio-dev-portfolio', 'nebula-analytics-saas',
                  'halo-ai-writer', 'ascend-saas-landing'] as $slug) {
            $this->assertContains($slug, $slugs->all(), "Listing yetishmayapti: {$slug}");
        }

        $this->assertGreaterThanOrEqual(11, $slugs->count());
    }

    public function test_buyruq_idempotent(): void
    {
        User::factory()->create();

        $this->artisan('marketplace:sync-listings')->assertSuccessful();
        $first = MarketplaceListing::count();

        $this->artisan('marketplace:sync-listings')->assertSuccessful();

        $this->assertSame($first, MarketplaceListing::count());
    }
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=SyncListingsCommandTest`
Kutilgan natija: FAIL — `The command "marketplace:sync-listings" does not exist.`

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`app/Console/Commands/SyncMarketplaceListings.php`:

```php
<?php

namespace App\Console\Commands;

use Database\Seeders\AscendListingSeeder;
use Database\Seeders\FitTrackListingSeeder;
use Database\Seeders\HaloListingSeeder;
use Database\Seeders\LandingPacksSeeder;
use Database\Seeders\LinkFolioListingSeeder;
use Database\Seeders\NebulaListingSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Barcha birinchi-tomon marketplace listinglarini ma'lumotlar bazasiga
 * yozadi.
 *
 * Mavjud bo'lish sababi: seeder'lar faqat testlardan chaqirilardi —
 * DatabaseSeeder ularni ro'yxatga olmagan, deploy.sh esa faqat
 * SiteSettingSeeder ni ishga tushirardi. Ya'ni har bir mahsulot
 * production'ga qo'lda chiqarilardi (spec §7).
 *
 * Barcha seeder'lar firstOrCreate/updateOrCreate ishlatadi, shuning uchun
 * bu buyruqni istalgancha qayta ishga tushirish xavfsiz.
 */
class SyncMarketplaceListings extends Command
{
    protected $signature = 'marketplace:sync-listings';
    protected $description = 'Barcha birinchi-tomon marketplace listinglarini idempotent tarzda yozadi';

    /** @var list<class-string> */
    private const SEEDERS = [
        FitTrackListingSeeder::class,
        LinkFolioListingSeeder::class,
        NebulaListingSeeder::class,
        HaloListingSeeder::class,
        AscendListingSeeder::class,
        LandingPacksSeeder::class,
    ];

    public function handle(): int
    {
        foreach (self::SEEDERS as $seeder) {
            $this->line('  ' . class_basename($seeder));
            (new $seeder())->run();
        }

        $count = \App\Models\MarketplaceListing::count();
        $this->info("✅ {$count} ta listing sinxronlandi.");
        Log::info('marketplace:sync-listings completed', ['listings' => $count]);

        return self::SUCCESS;
    }
}
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=SyncListingsCommandTest`
Kutilgan natija: PASS — 2 ta test.

- [ ] **Qadam 5: `deploy.sh` ga ulang**

`deploy.sh` da `db:seed --class=SiteSettingSeeder` qatoridan **keyin** qo'shing:

```bash
# Sync first-party marketplace listings (idempotent).
echo "🛍️  Syncing marketplace listings..."
php artisan marketplace:sync-listings || fail "marketplace:sync-listings"
```

`bash -n deploy.sh` bilan sintaksisni tekshiring.

- [ ] **Qadam 6: Commit qiling**

```bash
git add app/Console/Commands/SyncMarketplaceListings.php tests/Feature/Marketplace/SyncListingsCommandTest.php deploy.sh
git commit -m "feat(marketplace): idempotent listing sync wired into deploy

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 2: Tier o'zgarishlari — `prompt` bepul, `code` nashrdan olinadi

Spec §7: `code` tier hech qachon fayl bilan ta'minlanmagan va allaqachon sotilmaydi. `prompt` esa magnitga aylanadi.

**Fayllar:**
- O'zgartirish: `database/seeders/Concerns/SeedsMarketplaceListing.php`
- Test: `tests/Feature/Marketplace/SyncListingsCommandTest.php`

**Interfeyslar:**
- Beradi: `prompt` tier `is_free = true`, `status = 'published'`; `code` tier `status = 'archived'`.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`SyncListingsCommandTest.php` ga qo'shing:

```php
    public function test_prompt_tier_bepul_code_tier_nashrdan_olingan(): void
    {
        User::factory()->create();
        $this->artisan('marketplace:sync-listings')->assertSuccessful();

        $prompts = \App\Models\DigitalProduct::where('tier', 'prompt')->get();
        $this->assertNotEmpty($prompts, 'prompt tier topilmadi');

        foreach ($prompts as $prompt) {
            $this->assertTrue((bool) $prompt->is_free, "prompt tier bepul emas: {$prompt->id}");
            $this->assertSame('published', $prompt->status);
            $this->assertNotNull($prompt->file_path, 'prompt tier deliverable yo\'q');
        }

        foreach (\App\Models\DigitalProduct::where('tier', 'code')->get() as $code) {
            $this->assertSame('archived', $code->status, 'code tier hamon nashrda');
        }

        // design va bundle o'zgarmasligi kerak
        foreach (\App\Models\DigitalProduct::whereIn('tier', ['design', 'bundle'])->get() as $paid) {
            $this->assertFalse((bool) $paid->is_free, "{$paid->tier} bepul bo'lib qolgan");
            $this->assertSame('published', $paid->status);
        }
    }
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=test_prompt_tier_bepul_code_tier_nashrdan_olingan`
Kutilgan natija: FAIL — `prompt tier bepul emas`.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`SeedsMarketplaceListing.php` ichida, `DigitalProduct::updateOrCreate(...)` chaqiruvida `'is_free'` va `'status'` qatorlarini almashtiring:

```php
                    // prompt tier — bepul magnit (spec §7, qaror D1).
                    // code tier hech qachon deliverable olmagan, shuning uchun
                    // arxivlanadi: o'lik qatorni o'chirish, mahsulot yo'nalishini emas.
                    'is_free'                  => $t['tier'] === 'prompt' ? true : ($t['is_free'] ?? false),
                    'status'                   => $t['tier'] === 'code' ? 'archived' : 'published',
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=SyncListingsCommandTest`
Kutilgan natija: PASS — 3 ta test.

Keyin to'liq to'plam: `php artisan test` — mavjud marketplace testlari buzilmasin. Agar `MarketplacePurchaseTest` yiqilsa, u `prompt` tier'ni pullik deb kutayotgan bo'lishi mumkin — testni yangi haqiqatga moslang, implementatsiyani emas, va nima o'zgartirganingizni hisobotda yozing.

- [ ] **Qadam 5: Commit qiling**

```bash
git add database/seeders/Concerns/SeedsMarketplaceListing.php tests/Feature/Marketplace/SyncListingsCommandTest.php
git commit -m "feat(marketplace): prompt tier becomes free, dead code tier archived

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 3: Email darvozasi — hisobsiz so'rov

**Fayllar:**
- Yaratish: `app/Http/Controllers/PromptGateController.php`
- O'zgartirish: `routes/web.php`
- Test: `tests/Feature/Marketplace/PromptGateTest.php`

**Interfeyslar:**
- Beradi: `POST /market/{listing:slug}/prompt` → `marketplace.prompt.request`.
- Obunaning `preferences` JSON'ida `pending_prompt_listing` kaliti — Vazifa 5 shuni o'qiydi.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Feature/Marketplace/PromptGateTest.php`:

```php
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
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=PromptGateTest`
Kutilgan natija: FAIL — `Route [marketplace.prompt.request] not defined.`

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`app/Http/Controllers/PromptGateController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\NewsletterSubscription;
use App\Services\NewsletterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Bepul prompt magnitining email darvozasi (spec §7).
 *
 * Ataylab hisob TALAB QILMAYDI: begona odam bilan eng kuchli aktiv
 * o'rtasida ro'yxatdan o'tish devori bo'lsa, magnitning ma'nosi qolmaydi.
 * Mavjud double-opt-in tokeni yagona ishqalanish nuqtasi bo'lib qoladi.
 *
 * Bu yerda ProductPurchase YARATILMAYDI: email yig'ish sotuv emas, va
 * ikkisini aralashtirish sales_count ni buzadi.
 */
class PromptGateController extends Controller
{
    public function __construct(private readonly NewsletterService $newsletter)
    {
    }

    public function request(Request $request, MarketplaceListing $listing): RedirectResponse
    {
        abort_unless($listing->status === 'published', 404);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        try {
            // DIQQAT: NewsletterService::subscribe() updateOrCreate ishlatadi va
            // mavjud obunachining `frequency` hamda `preferences` maydonlarini
            // QAYTA YOZADI. Uni shartsiz chaqirsak, haftalik obunachi prompt
            // so'raganda uning xat chastotasi so'ramasdan kunlikka o'zgarardi —
            // bu foydalanuvchi sozlamasini jimgina buzish bo'ladi.
            // Shuning uchun mavjud obunachi uchun faqat preferences'ga
            // qo'shamiz (updatePreferences() birlashtiradi), chastotaga tegmaymiz.
            $existing = NewsletterSubscription::where('email', $validated['email'])->first();

            if ($existing) {
                $existing->updatePreferences(['pending_prompt_listing' => $listing->slug]);
                $subscription = $existing->fresh();

                if (! $subscription->verified_at) {
                    $this->newsletter->sendVerificationEmail($subscription);
                }
            } else {
                $subscription = $this->newsletter->subscribe(
                    $validated['email'],
                    null,
                    'daily',
                    ['pending_prompt_listing' => $listing->slug]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Prompt gate subscribe failed', [
                'listing' => $listing->slug,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Emailni qabul qilib bo\'lmadi. Birozdan keyin urinib ko\'ring.');
        }

        if ($subscription->verified_at) {
            return redirect()->to($this->newsletter->promptDownloadUrl($subscription, $listing));
        }

        return back()->with('success', 'Tasdiqlash havolasini emailingizga yubordik — bosing va prompt sizniki.');
    }
}
```

`routes/web.php` da marketplace guruhiga qo'shing:

```php
    Route::post('/{listing:slug}/prompt', [\App\Http\Controllers\PromptGateController::class, 'request'])
        ->middleware('throttle:5,1')->name('prompt.request');
```

`NewsletterService` ga vaqtincha yordamchi qo'shing (Vazifa 4 uni to'ldiradi):

```php
    /**
     * Obunachi uchun bitta listing promptiga vaqtinchalik imzolangan havola.
     */
    public function promptDownloadUrl(NewsletterSubscription $subscription, \App\Models\MarketplaceListing $listing): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'marketplace.prompt.download',
            now()->addDays(7),
            ['listing' => $listing->slug, 'subscription' => $subscription->id]
        );
    }
```

> Bu bosqichda `marketplace.prompt.download` marshruti hali yo'q — Vazifa 4 uni yaratadi. Shuning uchun bu vazifadagi testlar **tasdiqlanmagan** obunachi yo'lini tekshiradi (yo'naltirish chaqirilmaydi).

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=PromptGateTest`
Kutilgan natija: PASS — 3 ta test.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Http/Controllers/PromptGateController.php app/Services/NewsletterService.php routes/web.php tests/Feature/Marketplace/PromptGateTest.php
git commit -m "feat(marketplace): accept an email for the free prompt without an account

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 4: Imzolangan yuklab olish marshruti

**Fayllar:**
- O'zgartirish: `app/Http/Controllers/PromptGateController.php`, `routes/web.php`
- Test: `tests/Feature/Marketplace/PromptGateTest.php`

**Interfeyslar:**
- Beradi: `GET /market/{listing:slug}/prompt/{subscription}` → `marketplace.prompt.download`, `signed` middleware bilan.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`PromptGateTest.php` ga qo'shing:

```php
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
        $this->assertSame(
            'application/octet-stream',
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
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=PromptGateTest`
Kutilgan natija: FAIL — `Route [marketplace.prompt.download] not defined.`

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`PromptGateController` ga qo'shing:

```php
    public function download(MarketplaceListing $listing, NewsletterSubscription $subscription): StreamedResponse
    {
        abort_unless($listing->status === 'published', 404);

        $product = $listing->tiers()
            ->where('tier', 'prompt')
            ->whereNotNull('file_path')
            ->first();

        abort_if($product === null, 404, 'Bu mahsulot uchun prompt hali tayyor emas.');
        abort_unless(Storage::disk('private')->exists($product->file_path), 404);

        Log::info('Prompt magnet delivered', [
            'listing' => $listing->slug,
            'subscription_id' => $subscription->id,
        ]);

        return Storage::disk('private')->download(
            $product->file_path,
            $listing->slug . '-prompt.md'
        );
    }
```

Kerakli importlar (`NewsletterSubscription` Vazifa 3 da allaqachon qo'shilgan — takrorlamang):

```php
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
```

`routes/web.php` marketplace guruhiga:

```php
    Route::get('/{listing:slug}/prompt/{subscription}', [\App\Http\Controllers\PromptGateController::class, 'download'])
        ->middleware('signed')->name('prompt.download');
```

> **Diqqat:** bu marshrut `GET /market/{listing:slug}` wildcard'idan **keyin** emas, **oldin** e'lon qilinishi kerak emas — yo'l segmentlari soni har xil, shuning uchun to'qnashuv yo'q. Lekin `marketplace.show` marshrutidan keyin qo'yilsa ham ishlaydi; joylashuvni mavjud guruh ichida saqlang.

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=PromptGateTest`
Kutilgan natija: PASS — 6 ta test.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Http/Controllers/PromptGateController.php routes/web.php tests/Feature/Marketplace/PromptGateTest.php
git commit -m "feat(marketplace): deliver the free prompt over a temporary signed URL

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 5: Tasdiqlash → yuklab olishga yo'naltirish

Bitta harakat ham obunani tasdiqlaydi, ham faylni yetkazadi.

**Fayllar:**
- O'zgartirish: `app/Http/Controllers/NewsletterController.php`
- Test: `tests/Feature/Marketplace/PromptGateTest.php`

- [ ] **Qadam 1: Yiqiladigan testni yozing**

```php
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
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=test_tasdiqlash_promptga_yonaltiradi`
Kutilgan natija: FAIL — yo'naltirish o'rniga 200 qaytadi.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`NewsletterController::verify()` ni almashtiring:

```php
    public function verify($token)
    {
        $subscription = $this->newsletterService->verify($token);

        if (! $subscription) {
            return view('newsletter.verify-failed');
        }

        // Prompt magniti orqali kelgan bo'lsa, tasdiqlash bitta harakatda
        // faylni ham yetkazadi (spec §7): bitta ishqalanish nuqtasi.
        $slug = data_get($subscription->preferences, 'pending_prompt_listing');

        if ($slug) {
            $listing = \App\Models\MarketplaceListing::where('slug', $slug)
                ->where('status', 'published')
                ->first();

            if ($listing) {
                return redirect()->to(
                    $this->newsletterService->promptDownloadUrl($subscription, $listing)
                );
            }
        }

        return view('newsletter.verified', compact('subscription'));
    }
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=PromptGateTest`
Kutilgan natija: PASS — 8 ta test.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Http/Controllers/NewsletterController.php tests/Feature/Marketplace/PromptGateTest.php
git commit -m "feat(newsletter): verifying a prompt-gate signup delivers the file

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 6: Marketplace sahifasida email formasi

**Fayllar:**
- O'zgartirish: `resources/views/marketplace/show.blade.php`
- Test: `tests/Feature/Marketplace/PromptGateTest.php`

- [ ] **Qadam 1: Yiqiladigan testni yozing**

```php
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
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=test_sahifada_prompt_uchun_email_formasi_bor`
Kutilgan natija: FAIL — forma marshruti sahifada yo'q.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`resources/views/marketplace/show.blade.php` da tier siklini toping (`@forelse($listing->tiers as $tier)`) va **arxivlanganlarni chiqarib tashlang** hamda `prompt` tier uchun forma ko'rsating. Tier `<div class="tier">` blokining ichini shunday qiling:

```blade
            @forelse($listing->tiers->where('status', 'published') as $tier)
              <div class="tier">
                <div>
                  <div style="font-weight:700; text-transform:capitalize;">{{ $tier->tier ?? $tier->type }}</div>
                  <div style="font-size:.78rem; color:var(--ink-faint);">{{ Str::limit($tier->short_description ?? $tier->description, 42) }}</div>
                </div>
                @if($tier->tier === 'prompt')
                  <form method="POST" action="{{ route('marketplace.prompt.request', $listing) }}" style="display:flex; gap:6px; align-items:center;">
                    @csrf
                    <input type="email" name="email" required placeholder="siz@email.com"
                           aria-label="Promptni olish uchun email"
                           style="padding:7px 10px; border:1.5px solid var(--line); border-radius:8px; font:inherit; min-width:170px;">
                    <button type="submit" class="t-price" style="cursor:pointer; border:0; background:var(--signal); color:#fff; padding:8px 12px; border-radius:8px;">Bepul olish</button>
                  </form>
                @else
                  <div class="t-price">${{ number_format((float) $tier->price, 2) }}</div>
                @endif
              </div>
            @empty
```

Forma ustida sessiya xabarlarini ko'rsating (tier ro'yxatidan oldin):

```blade
            @if(session('success'))
              <p style="font-size:.8rem; color:var(--signal); margin:0 0 10px;">{{ session('success') }}</p>
            @endif
            @error('email')
              <p style="font-size:.8rem; color:#b3313e; margin:0 0 10px;">{{ $message }}</p>
            @enderror
```

> Mavjud `t-price` uslubi va `--signal` tokeni saqlanadi — sahifaning dizayn tili o'zgarmaydi.

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=PromptGateTest`
Kutilgan natija: PASS — 10 ta test.

Keyin to'liq to'plam: `php artisan test`.

- [ ] **Qadam 5: Commit qiling**

```bash
git add resources/views/marketplace/show.blade.php tests/Feature/Marketplace/PromptGateTest.php
git commit -m "feat(marketplace): swap the prompt tier's price for an email form

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 7: Kunlik digest

Quvur allaqachon mavjud: `frequency` enum'ida `daily` bor va `sendCampaign()` frequency qabul qiladi.

**Fayllar:**
- O'zgartirish: `app/Services/NewsletterService.php`
- Yaratish: `app/Console/Commands/SendDailyNewsletter.php`
- O'zgartirish: `routes/console.php`
- Test: `tests/Feature/Newsletter/DailyDigestTest.php`

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Feature/Newsletter/DailyDigestTest.php`:

```php
<?php

namespace Tests\Feature\Newsletter;

use App\Models\NewsletterCampaign;
use App\Models\Post;
use App\Services\NewsletterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_kunlik_digest_kampaniya_yaratadi(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subHours(3),
            'title' => 'Bugungi maqola sarlavhasi',
        ]);

        $campaign = app(NewsletterService::class)->generateDailyDigest();

        $this->assertInstanceOf(NewsletterCampaign::class, $campaign);
        $this->assertSame('digest', $campaign->type);
        $this->assertStringContainsString('Bugungi maqola sarlavhasi', $campaign->content);
    }

    public function test_aytadigan_narsa_bolmasa_digest_yaratilmaydi(): void
    {
        Post::where('status', 'published')->delete();

        $this->assertNull(app(NewsletterService::class)->generateDailyDigest());
    }

    public function test_buyruq_bosh_digestda_hech_narsa_yubormaydi(): void
    {
        Post::where('status', 'published')->delete();

        $this->artisan('newsletter:send-daily')
            ->expectsOutputToContain('yuboriladigan')
            ->assertSuccessful();

        $this->assertSame(0, NewsletterCampaign::count());
    }
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=DailyDigestTest`
Kutilgan natija: FAIL — `Call to undefined method ...::generateDailyDigest()`.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`NewsletterService` ga qo'shing (mavjud `generateWeeklyDigest()` yonida, uning uslubiga ergashib):

```php
    /**
     * So'nggi 24 soatda nashr qilingan postlardan kunlik digest.
     *
     * Aytadigan narsa bo'lmasa NULL qaytaradi — bo'sh kunlik xat
     * obunadan chiqishga olib keladi (spec §10).
     */
    public function generateDailyDigest(): ?NewsletterCampaign
    {
        $posts = \App\Models\Post::where('status', 'published')
            ->where('published_at', '>=', now()->subDay())
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        if ($posts->isEmpty()) {
            return null;
        }

        $content = "<h2>Bugun NextGenBeing'da</h2>\n";
        foreach ($posts as $post) {
            $url = route('posts.show', $post->slug);
            $content .= '<p><a href="' . e($url) . '">' . e($post->title) . '</a><br>'
                . e(\Illuminate\Support\Str::limit(strip_tags((string) $post->excerpt), 140)) . "</p>\n";
        }

        return NewsletterCampaign::create([
            'subject' => 'NextGenBeing — ' . now()->format('d.m.Y'),
            'content' => $content,
            'type' => 'digest',
            'status' => 'draft',
        ]);
    }
```

`app/Console/Commands/SendDailyNewsletter.php`:

```php
<?php

namespace App\Console\Commands;

use App\Services\NewsletterService;
use Illuminate\Console\Command;

class SendDailyNewsletter extends Command
{
    protected $signature = 'newsletter:send-daily';
    protected $description = 'Kunlik digestni daily obunachilarga yuboradi';

    public function __construct(private readonly NewsletterService $newsletter)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $campaign = $this->newsletter->generateDailyDigest();

        if ($campaign === null) {
            $this->info('Bugun yuboriladigan yangilik yo\'q — digest o\'tkazib yuborildi.');

            return self::SUCCESS;
        }

        $sent = $this->newsletter->sendCampaign($campaign, 'daily');
        $this->info("✅ Kunlik digest {$sent} ta obunachiga yuborildi.");

        return self::SUCCESS;
    }
}
```

`routes/console.php` ga:

```php
// Kunlik digest — haftalik flagman chiqarish orasidagi kunlarni to'ldiradi (spec D2).
Schedule::command('newsletter:send-daily')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=DailyDigestTest`
Kutilgan natija: PASS — 3 ta test.

Keyin to'liq to'plam: `php artisan test`.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Services/NewsletterService.php app/Console/Commands/SendDailyNewsletter.php routes/console.php tests/Feature/Newsletter/DailyDigestTest.php
git commit -m "feat(newsletter): daily digest for daily-frequency subscribers

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Yakuniy tekshiruv (qo'lda, deploy'dan keyin)

- [ ] Production'da `php artisan marketplace:sync-listings` ishlaganini va 11 ta listing borligini tasdiqlang.
- [ ] Jonli saytda bitta listing sahifasini oching, o'z emailingizni kiriting, kelgan xatdagi havolani bosing va `.md` fayl yuklanishini tekshiring.
- [ ] `NewsletterSubscription::whereNotNull('verified_at')->count()` — bu C3 ning yagona haqiqiy metrikasi. Boshlang'ich qiymat: **1**.

## Bu rejada YO'Q narsalar

- C2 (trend mavzular) — alohida reja.
- Reyting, sharh, sotuvchi onboarding, ko'p mahsulotli savat — spec §3 bo'yicha ko'lamdan tashqarida.
- `design` va `bundle` tier narxlarini o'zgartirish — ular tegilmaydi.
- Haftalik flagman chiqarish **kontentini** yaratish — bu jarayon, kod emas.
