<?php

namespace Tests\Unit\Telemetry;

use App\Services\Telemetry\ErrorEvent;
use App\Services\Telemetry\ErrorReporter;
use App\Services\Telemetry\EventFormatter;
use App\Services\Telemetry\FeedWriter;
use App\Services\Telemetry\Redactor;
use App\Services\Telemetry\TelegramTransport;
use App\Services\Telemetry\Throttle;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Xato hisobotchisi testlari.
 *
 * Ma'lumotlar bazasi kerak emas — shuning uchun `Tests\TestCase` emas,
 * to'g'ridan-to'g'ri freymvork bazasi kengaytiriladi (u `RefreshDatabase`
 * ishlatmaydi va testlar bir necha barobar tez o'tadi).
 */
class ErrorReporterTest extends TestCase
{
    private const TOKEN = 'test-token';

    private const CHAT = '-1001234567890';

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    /** @param array<string, mixed> $overrides */
    private function reporter(array $overrides = []): ErrorReporter
    {
        $config = array_merge([
            'enabled' => true,
            'project' => 'Nextgenbeing',
            'env' => 'production',
            'environments' => ['production'],
            'release' => 'a3f91c2',
            'ignore_status' => [401, 403, 404, 422],
            'ignore_exceptions' => [AuthenticationException::class, NotFoundHttpException::class],
            'dedupe_ttl' => 600,
            'hourly_cap' => 40,
        ], $overrides);

        return new ErrorReporter(
            config: $config,
            throttle: new Throttle(
                cache: new Repository(new ArrayStore),
                dedupeTtl: (int) $config['dedupe_ttl'],
                hourlyCap: (int) $config['hourly_cap'],
            ),
            transport: new TelegramTransport(self::TOKEN, self::CHAT, '12'),
        );
    }

    public function test_maxfiy_malumot_hisobotga_tushmaydi(): void
    {
        $dirty = 'Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxIn0.abcdefghij '
            .'{"password": "12345678"} tel: +998901234567 ?api_key=zzz';

        $clean = Redactor::text($dirty);

        $this->assertStringNotContainsString('eyJhbGciOiJIUzI1NiJ9', $clean);
        $this->assertStringNotContainsString('12345678', $clean);
        $this->assertStringNotContainsString('+998901234567', $clean);
        $this->assertStringNotContainsString('zzz', $clean);
        $this->assertStringContainsString('<phone>', $clean);
    }

    public function test_maxfiy_kalitlar_massivda_ham_tozalanadi(): void
    {
        $clean = Redactor::array([
            'email' => 'a@b.uz',
            'password' => 'sirli',
            'nested' => ['access_token' => 'xyz', 'ok' => 'qoladi'],
        ]);

        $this->assertSame('<redacted>', $clean['password']);
        $this->assertSame('<redacted>', $clean['nested']['access_token']);
        $this->assertSame('qoladi', $clean['nested']['ok']);
        $this->assertSame('a@b.uz', $clean['email']);
    }

    public function test_bir_xil_xato_bir_xil_barmoq_izini_beradi(): void
    {
        $config = ['project' => 'Nextgenbeing', 'env' => 'production', 'release' => null];

        $a = ErrorEvent::fromThrowable(new RuntimeException('User 41 topilmadi'), [], $config);
        $b = ErrorEvent::fromThrowable(new RuntimeException('User 9312 topilmadi'), [], $config);
        $c = ErrorEvent::fromThrowable(new RuntimeException('Boshqa xato'), [], $config);

        // Raqamlar normallashtiriladi: aks holda har bir id yangi muammo
        // bo'lib ko'rinardi va bo'g'ish umuman ishlamasdi.
        $this->assertSame($a->fingerprint(), $b->fingerprint());
        $this->assertNotSame($a->fingerprint(), $c->fingerprint());
    }

    public function test_kundalik_4xx_lar_yuborilmaydi(): void
    {
        $reporter = $this->reporter();

        $this->assertFalse($reporter->shouldReport(new NotFoundHttpException));
        $this->assertFalse($reporter->shouldReport(new AuthenticationException));
        $this->assertFalse($reporter->shouldReport(new HttpException(422, 'Validation')));
        $this->assertTrue($reporter->shouldReport(new HttpException(500, 'Server')));
        $this->assertTrue($reporter->shouldReport(new RuntimeException('Yiqildi')));
    }

    public function test_xato_telegramga_yuboriladi(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $this->assertTrue($this->reporter()->report(new RuntimeException('Baza yiqildi')));

        Http::assertSent(function ($request): bool {
            $body = $request->data();

            $this->assertStringContainsString(self::TOKEN, $request->url());
            $this->assertSame(self::CHAT, $body['chat_id']);
            $this->assertSame(12, $body['message_thread_id']);
            $this->assertSame('HTML', $body['parse_mode']);
            $this->assertStringContainsString('Nextgenbeing', $body['text']);
            $this->assertStringContainsString('Baza yiqildi', $body['text']);
            $this->assertStringContainsString('#Nextgenbeing', $body['text']);

            return true;
        });
    }

    public function test_takroriy_xato_faqat_bir_marta_yuboriladi(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $reporter = $this->reporter();

        $this->assertTrue($reporter->report(new RuntimeException('Halqadagi xato')));
        $this->assertFalse($reporter->report(new RuntimeException('Halqadagi xato')));
        $this->assertFalse($reporter->report(new RuntimeException('Halqadagi xato')));

        Http::assertSentCount(1);
    }

    public function test_soatlik_chegaradan_keyin_bitta_ogohlantirish(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $reporter = $this->reporter(['hourly_cap' => 3]);

        for ($i = 0; $i < 6; $i++) {
            $reporter->report(new RuntimeException("Xato raqam {$i} turi ".chr(65 + $i)));
        }

        // 3 ta xabar + 1 ta "chegaraga yetildi" ogohlantirishi.
        Http::assertSentCount(4);
    }

    public function test_ruxsat_etilmagan_muhitda_yuborilmaydi(): void
    {
        Http::fake();

        $reporter = $this->reporter(['env' => 'local']);

        $this->assertFalse($reporter->enabled());
        $this->assertFalse($reporter->report(new RuntimeException('Local xato')));

        Http::assertNothingSent();
    }

    public function test_telegram_yiqilsa_ham_istisno_tashlanmaydi(): void
    {
        Http::fake(fn () => throw new RuntimeException('tarmoq yo\'q'));

        // Hisobot mexanizmi ilovani yiqitmasligi kerak — eng muhim kafolat.
        $this->assertFalse($this->reporter()->report(new RuntimeException('Asl xato')));
    }

    public function test_ilovadan_kelgan_hisobot_app_turida_ketadi(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $sent = $this->reporter()->reportClient([
            'kind' => 'app',
            'error' => 'StateError: Bad state: No element',
            'stack' => "#0 _List.first (dart:core)\n#1 PrayerScreen.build (package:muslim_stack/screens/prayer.dart:88)",
            'appVersion' => '2.1.0+14',
            'platform' => 'android',
            'userId' => '1841',
            'screen' => 'PrayerScreen',
            'breadcrumbs' => ['12:00:01 http GET /api/v1/prayer/times → 200'],
        ]);

        $this->assertTrue($sent);

        Http::assertSent(function ($request): bool {
            $text = $request->data()['text'];

            $this->assertStringContainsString('#app', $text);
            $this->assertStringContainsString('StateError', $text);
            // Culprit ilova kodidan olinadi, Flutter kutubxonasidan emas.
            $this->assertStringContainsString('package:muslim_stack/screens/prayer.dart', $text);
            $this->assertStringContainsString('1841', $text);

            return true;
        });
    }

    public function test_juda_uzun_xabar_teglarni_buzmasdan_kesiladi(): void
    {
        $event = new ErrorEvent(
            project: 'Nextgenbeing',
            env: 'production',
            kind: 'http',
            type: 'RuntimeException',
            message: 'Uzun xato',
            culprit: 'app/X.php:1',
            stack: [str_repeat('#0 app/Very/Long/Path.php:123 handle()  ', 300)],
        );

        $text = EventFormatter::telegram($event, 'abc123');

        $this->assertLessThanOrEqual(4096, mb_strlen($text));
        $this->assertStringContainsString('#abc123', $text);
        $this->assertSame(
            substr_count($text, '<pre>'),
            substr_count($text, '</pre>'),
            'Kesilgan xabarda <pre> ochiq qolsa, Telegram butun xabarni rad etadi'
        );
    }

    public function test_jarvis_lentasiga_json_qator_yoziladi_va_takror_bosiladi(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $path = sys_get_temp_dir().'/errfeed-'.uniqid().'.jsonl';
        $cache = new Repository(new ArrayStore);

        $reporter = new ErrorReporter(
            config: ['enabled' => true, 'project' => 'Nextgenbeing', 'env' => 'production',
                'environments' => ['production'], 'dedupe_ttl' => 600, 'hourly_cap' => 40],
            throttle: new Throttle($cache),
            transport: new TelegramTransport(self::TOKEN, self::CHAT, '12'),
            feed: new FeedWriter($path, $cache),
        );

        $reporter->report(new RuntimeException('Lenta sinovi 1'));
        $reporter->report(new RuntimeException('Lenta sinovi 2'));

        $lines = array_values(array_filter(explode("
", (string) file_get_contents($path))));
        @unlink($path);

        $this->assertCount(1, $lines, 'bir xil barmoq izi daqiqada bir marta yoziladi');
        $row = json_decode($lines[0], true);
        $this->assertSame('Nextgenbeing', $row['project']);
        $this->assertSame('RuntimeException', $row['type']);
        $this->assertStringContainsString('Lenta sinovi', $row['message']);
        $this->assertNotEmpty($row['fingerprint']);
        $this->assertNotEmpty($row['id']);
    }
}
