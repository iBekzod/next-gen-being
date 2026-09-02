<?php

namespace Tests\Feature\Content;

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
            ->expectsOutputToContain('empty_publishable_backlog')
            ->assertExitCode(1);
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
}
