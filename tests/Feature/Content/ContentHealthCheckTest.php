<?php

namespace Tests\Feature\Content;

use App\Models\CollectedContent;
use App\Models\ContentSource;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\MakesGateContent;
use Tests\TestCase;

class ContentHealthCheckTest extends TestCase
{
    use RefreshDatabase;
    use MakesGateContent;

    protected function setUp(): void
    {
        parent::setUp();

        // DatabaseSeeder (via $seed = true on the base TestCase) seeds several
        // published, non-tutorial posts via ContentSeeder and FeedDemoSeeder
        // (FeedDemoSeeder's are always published_at = now()->subHours(rand(6,72)),
        // i.e. always < 3 days old). Left in place, those posts satisfy the
        // cadence checks in content:health-check regardless of what this test
        // sets up, making every assertion here pass/fail for the wrong reason.
        // Clear them so staleness/backlog checks are driven only by each test's
        // own fixtures. Same pattern as ContentDripPublishTest::setUp().
        Post::where('status', 'published')->whereNull('series_title')->delete();
    }

    public function test_eski_korpus_ogohlantiradi(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'series_title' => null,
            'published_at' => now()->subDays(30),
            'content' => $this->cleanContent(),
        ]);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('stale_posts')
            ->assertExitCode(1);
    }

    public function test_nashrga_tayyor_draft_yoqligi_ogohlantiradi(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'series_title' => null,
            'published_at' => now(),
            'content' => $this->cleanContent(),
        ]);
        Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => 'approved',
            'content' => 'Juda qisqa draft.',
        ]);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('empty_publishable_post_backlog')
            ->assertExitCode(1);
    }

    /**
     * Uchta yaxshi POST tutorial ochligini yashirmasligi kerak: zaxira
     * hovuzlari alohida sanaladi, chegara esa ikkalasi uchun bir xil.
     */
    public function test_tutorial_zaxirasi_alohida_sanaladi(): void
    {
        Post::where('status', 'draft')->delete();

        $this->makeFreshPublished();

        // Faqat postlar tomonida to'liq zaxira; tutoriallar tomoni bo'sh.
        $this->makePublishableDrafts(3, null);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('empty_publishable_tutorial_backlog')
            ->doesntExpectOutputToContain('empty_publishable_post_backlog')
            ->assertExitCode(1);
    }

    /**
     * Sog'lom holat 0 bilan tugashi SHART — aks holda buyruq har doim
     * "buzilgan" deb qichqiradi va ogohlantirish qiymatini yo'qotadi.
     * Hech bir mavjud test 0 chiqish kodini isbotlamagan edi.
     */
    public function test_soglom_quvur_nol_kod_bilan_tugaydi(): void
    {
        // setUp() faqat nashr qilingan POSTlarni tozalaydi; seed qilingan
        // draftlar sanoqqa aralashmasligi uchun ularni ham olib tashlaymiz.
        Post::where('status', 'draft')->delete();

        $this->makeFreshPublished();
        $this->makePublishableDrafts(3, null);
        $this->makePublishableDrafts(3, 'Laravel navbatlari seriyasi');

        // Testning maqsadi "sog'lom quvur 0 kod bilan tugaydi", "nol faol
        // manba sog'lom holat" emas — shuning uchun nol faol manba endi
        // o'zi alohida muammo (`no_active_sources`) bo'lgani sababli, bu
        // yerda kamida bitta faol, yaqinda yig'ilgan manba bo'lishi kerak,
        // aks holda test "sog'lom" holatni emas, balki yangi muammoni sinaydi.
        \App\Models\ContentSource::create([
            'name' => 'Alpha', 'url' => 'https://a.example', 'category' => 'news',
            'trust_level' => 90, 'scraping_enabled' => true,
            'last_scraped_at' => now()->subHours(2),
        ]);

        // Yig'ish yangi bo'lsa-yu trend navbati bo'sh qolsa — bu endi
        // alohida muammo (`empty_topic_queue`). Shuning uchun "sog'lom"
        // holat ikki mustaqil manba tasdiqlagan kamida bitta klasterni
        // ham talab qiladi, aks holda bu test yana yangi muammoni sinaydi.
        $this->makeQualifyingCluster();

        $this->artisan('content:health-check')
            ->expectsOutputToContain("Kontent quvuri sog'lom")
            ->assertExitCode(0);
    }

    /** Yaqinda nashr qilingan bitta post va bitta tutorial. */
    private function makeFreshPublished(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'series_title' => null,
            'published_at' => now(),
            'content' => $this->cleanContent(),
        ]);

        Post::factory()->create([
            'status' => 'published',
            'series_title' => 'Laravel navbatlari seriyasi',
            'published_at' => now(),
            'content' => $this->cleanContent(),
        ]);
    }

    /** Darvozadan o'tadigan draftlar (moderatsiya tasdiqlangan). */
    private function makePublishableDrafts(int $count, ?string $seriesTitle): void
    {
        for ($i = 0; $i < $count; $i++) {
            Post::factory()->create([
                'status' => 'draft',
                'series_title' => $seriesTitle,
                'moderation_status' => 'approved',
                'content' => $this->codeHeavyContent(),
            ]);
        }
    }

    public function test_kutilayotgan_moderatsiya_ogohlantiradi(): void
    {
        Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => 'pending',
            'content' => $this->cleanContent(),
        ]);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('unattended_moderation')
            ->assertExitCode(1);
    }

    /**
     * Creates a single problem (unattended moderation) so handle() collects
     * $problems and calls notify() — the minimum degraded state needed by
     * the mail-recipient tests below.
     */
    private function makeDegradedState(): void
    {
        Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => 'pending',
            'content' => $this->cleanContent(),
        ]);
    }

    // NOTE on approach: Mail::fake() cannot be used to assert Mail::raw() calls
    // in this Laravel version — Illuminate\Support\Testing\Fakes\MailFake::raw()
    // is a literal no-op (see vendor/laravel/framework/src/Illuminate/Support/
    // Testing/Fakes/MailFake.php around the "Send a new message with only a raw
    // text part" doc block), and MailFake::send() only records messages that are
    // instanceof Mailable, which the ['raw' => $text] array passed by Mail::raw()
    // never is. So Mail::fake()->assertSentCount(1) would report 0 unconditionally,
    // regardless of whether notify() resolves the recipient correctly.
    //
    // Instead these tests use a narrower seam: MAIL_MAILER=array in testing (see
    // .env.testing / phpunit.xml) means Mail::raw() really runs through the real
    // Mailer into Illuminate\Mail\Transport\ArrayTransport, an in-memory Symfony
    // transport that never touches the network but does record the real,
    // fully-built Symfony\Component\Mime\Email — including the resolved "to"
    // address — exactly as production would send it. This verifies the actual
    // recipient resolution end-to-end rather than merely that "something" mail-like
    // was invoked.
    public function test_ogohlantirish_sozlangan_manzilga_yuboriladi(): void
    {
        config(['services.content_alert.email' => 'alerts@example.org']);

        $this->makeDegradedState();

        /** @var \Illuminate\Mail\Transport\ArrayTransport $transport */
        $transport = Mail::getSymfonyTransport();
        $transport->flush();

        $this->artisan('content:health-check')->assertExitCode(1);

        $messages = $transport->messages();
        $this->assertCount(1, $messages, 'Expected exactly one alert email to be sent.');

        $to = $messages->first()->getOriginalMessage()->getTo();
        $this->assertNotEmpty($to);
        $this->assertSame('alerts@example.org', $to[0]->getAddress());
    }

    public function test_example_com_manzilga_ogohlantirish_yuborilmaydi(): void
    {
        // No CONTENT_ALERT_EMAIL configured; the mail.from.address fallback
        // is the config/mail.php placeholder ("hello@example.com" per .env).
        // The example.com guard in notify() must suppress sending.
        config([
            'services.content_alert.email' => null,
            'mail.from.address' => 'hello@example.com',
        ]);

        $this->makeDegradedState();

        /** @var \Illuminate\Mail\Transport\ArrayTransport $transport */
        $transport = Mail::getSymfonyTransport();
        $transport->flush();

        $this->artisan('content:health-check')->assertExitCode(1);

        $this->assertCount(0, $transport->messages(), 'No mail should be sent to an example.com placeholder address.');
    }

    public function test_dry_run_rejimida_xat_yuborilmaydi(): void
    {
        config(['services.content_alert.email' => 'alerts@example.org']);

        $this->makeDegradedState();

        /** @var \Illuminate\Mail\Transport\ArrayTransport $transport */
        $transport = Mail::getSymfonyTransport();
        $transport->flush();

        $this->artisan('content:health-check', ['--dry-run' => true])->assertExitCode(1);

        $this->assertCount(0, $transport->messages(), '--dry-run must never send mail.');
    }

    /**
     * Nol faol manba — o'zi alohida, aniqroq muammo (`no_active_sources`),
     * `stale_scraping` emas. Production 2026-01 dan beri `content:init-sources`
     * ishga tushirilmagan holda deploy bo'ladi, ya'ni bu — kutilgan/gipotetik
     * emas, deploy kunidagi haqiqiy holat: mavzular navbati hech qachon
     * to'lmaydi, health-check esa buni aytmasa "sog'lom" ko'rinib qoladi.
     */
    public function test_faol_manba_yoqligi_ogohlantiradi(): void
    {
        $this->assertSame(0, \App\Models\ContentSource::active()->count());

        $this->artisan('content:health-check --dry-run')
            ->expectsOutputToContain('no_active_sources')
            ->doesntExpectOutputToContain('stale_scraping')
            ->assertExitCode(1);
    }

    public function test_eskirgan_scraping_ogohlantiradi(): void
    {
        \App\Models\ContentSource::create([
            'name' => 'Alpha', 'url' => 'https://a.example', 'category' => 'news',
            'trust_level' => 90, 'scraping_enabled' => true,
            'last_scraped_at' => now()->subDays(3),
        ]);

        $this->artisan('content:health-check --dry-run')
            ->expectsOutputToContain('stale_scraping')
            ->doesntExpectOutputToContain('no_active_sources')
            ->assertExitCode(1);
    }

    public function test_yangi_scraping_ogohlantirmaydi(): void
    {
        \App\Models\ContentSource::create([
            'name' => 'Alpha', 'url' => 'https://a.example', 'category' => 'news',
            'trust_level' => 90, 'scraping_enabled' => true,
            'last_scraped_at' => now()->subHours(2),
        ]);

        $this->artisan('content:health-check --dry-run')
            ->doesntExpectOutputToContain('stale_scraping')
            ->doesntExpectOutputToContain('no_active_sources');

        // Yuqoridagi doesntExpectOutputToContain o'zi kuchsiz: butun
        // stale_scraping blokini o'chirib tashlasa ham shu assertion o'tadi.
        // Shuning uchun quyida musbat isbot: qolgan quvur ham sog'lom
        // qilib qurilsa (yangi post/tutorial, to'liq draft zaxirasi,
        // moderatsiyada kutayotgan draft yo'q) va manba yaqinda yig'ilgan
        // bo'lsa, buyruq aniq 0 kod bilan tugashi kerak — bu "yangi
        // scraping" yo'lining haqiqatan ham ishlab, muammo qo'shmasligini
        // isbotlaydi.
        Post::where('status', 'draft')->delete();

        $this->makeFreshPublished();
        $this->makePublishableDrafts(3, null);
        $this->makePublishableDrafts(3, 'Laravel navbatlari seriyasi');
        $this->makeQualifyingCluster();

        $this->artisan('content:health-check')
            ->expectsOutputToContain("Kontent quvuri sog'lom")
            ->assertExitCode(0);
    }

    /**
     * Bo'sh trend navbati — UCHINCHI, alohida nosozlik. Manbalar sozlangan
     * VA yaqinda yig'ilgan (ya'ni `no_active_sources` ham, `stale_scraping`
     * ham tinch), lekin birorta mavzu ikkinchi mustaqil manba bilan
     * tasdiqlanmagan. Bu holat production'da jimgina o'tib ketardi:
     * `content:rank-topics` bo'sh navbatni SUCCESS bilan chiqaradi va
     * generatsiya eski kalit-so'z evristikasiga qaytib har doim biror
     * natija beradi, ya'ni tizim tashqaridan sog'lom ko'rinadi.
     */
    public function test_bosh_mavzular_navbati_ogohlantiradi(): void
    {
        ContentSource::create([
            'name' => 'Alpha', 'url' => 'https://a.example', 'category' => 'news',
            'trust_level' => 90, 'scraping_enabled' => true,
            'last_scraped_at' => now()->subHours(2),
        ]);

        // Yig'ilgan kontent bor, lekin hammasi BITTA manbadan — korroboratsiya yo'q.
        $this->collected('Alpha', "Yolg'iz manba yozgan mavzu");

        $this->artisan('content:health-check --dry-run')
            ->expectsOutputToContain('empty_topic_queue')
            ->doesntExpectOutputToContain('stale_scraping')
            ->doesntExpectOutputToContain('no_active_sources')
            ->assertExitCode(1);
    }

    /**
     * Ikki mustaqil manba tasdiqlagan bitta klaster — trend navbati
     * to'ladigan minimal holat. `duplicate_of` bu yerda qo'lda yoziladi:
     * bu test klasterlashni emas, health-check mantiqini sinaydi
     * (klasterlashning o'zi DeduplicationIntegrationTest'da o'lchanadi).
     */
    private function makeQualifyingCluster(): void
    {
        $primary = $this->collected('Corroborating Alpha', "Postgres 18 pooling o'zgarishi");
        $this->collected('Corroborating Beta', 'Postgres 18 pooling qayta ishlandi', $primary->id);
    }

    private function collected(string $sourceName, string $title, ?int $primaryId = null): CollectedContent
    {
        $source = ContentSource::firstOrCreate(
            ['name' => $sourceName],
            [
                'url' => 'https://' . str_replace(' ', '-', strtolower($sourceName)) . '.example',
                'category' => 'news',
                'trust_level' => 90,
                'scraping_enabled' => true,
                'last_scraped_at' => now()->subHours(2),
            ]
        );

        return CollectedContent::create([
            'content_source_id' => $source->id,
            'external_url' => 'https://x.example/' . uniqid('', true),
            'title' => $title,
            'excerpt' => 'Excerpt for ' . $title,
            'full_content' => str_repeat('Body text about the subject. ', 20),
            'content_type' => 'article',
            'published_at' => now()->subHours(2),
            'is_duplicate' => $primaryId !== null,
            'duplicate_of' => $primaryId,
        ]);
    }
}
