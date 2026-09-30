<?php

namespace App\Services\Telemetry;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Xato hisobotchisi — soddalashtirilgan Sentry.
 *
 * Chaqiruvchi tomon faqat `report($e)` deydi; guruhlash, bo'g'ish,
 * tozalash va formatlash shu yerda. Telegram haqida faqat transport
 * biladi — ertaga boshqa joyga yuborilsa, chaqiruv joylari tegilmaydi.
 *
 * Kirish nuqtalari: `bootstrap/app.php` dagi istisno ushlagichi va
 * `Queue::failing` (JobFailed) tinglovchisi.
 */
final class ErrorReporter
{
    private readonly Breadcrumbs $crumbs;

    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly array $config,
        private readonly Throttle $throttle,
        private readonly TelegramTransport $transport,
        private readonly ?FeedWriter $feed = null,
    ) {
        $this->crumbs = new Breadcrumbs;
    }

    public function breadcrumbs(): Breadcrumbs
    {
        return $this->crumbs;
    }

    public function crumb(string $kind, string $message): void
    {
        try {
            $this->crumbs->add($kind, $message);
        } catch (Throwable) {
            // Nafas izi yozilmagani hech narsani buzmasligi kerak.
        }
    }

    /**
     * Serverdagi istisnoni yuboradi.
     *
     * @param  array<string, mixed>  $context
     */
    public function report(Throwable $e, array $context = []): bool
    {
        try {
            if (! $this->enabled() || ! $this->shouldReport($e)) {
                return false;
            }

            $event = ErrorEvent::fromThrowable($e, $this->context($context), [
                'project' => $this->config['project'],
                'env' => $this->config['env'],
                'release' => $this->release(),
            ]);

            return $this->dispatch($event);
        } catch (Throwable $inner) {
            // Hisobot mexanizmining o'zi ilovani yiqitmasligi kerak.
            Log::debug('[telemetry] report failed: '.$inner->getMessage());

            return false;
        }
    }

    /**
     * Mobil ilovadan kelgan hisobotni yuboradi.
     *
     * @param  array<string, mixed>  $payload
     */
    public function reportClient(array $payload): bool
    {
        try {
            if (! $this->enabled()) {
                return false;
            }

            $event = ErrorEvent::fromClient($payload, [
                'project' => $this->config['project'],
                'env' => $this->config['env'],
                'release' => $this->release(),
            ]);

            return $this->dispatch($event);
        } catch (Throwable $inner) {
            Log::debug('[telemetry] client report failed: '.$inner->getMessage());

            return false;
        }
    }

    /**
     * Bu istisno haqida xabar berilishi kerakmi.
     *
     * 401/403/404/422 — kundalik hodisa, xato emas: ular yuborilsa chat
     * shovqinga to'lib, haqiqiy nosozliklar ko'rinmay qoladi.
     */
    public function shouldReport(Throwable $e): bool
    {
        foreach ((array) ($this->config['ignore_exceptions'] ?? []) as $class) {
            if (is_string($class) && $e instanceof $class) {
                return false;
            }
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();

            if (in_array($status, (array) ($this->config['ignore_status'] ?? []), true)) {
                return false;
            }

            // 4xx — mijozning xatosi; server yiqilgani emas.
            if ($status < 500) {
                return false;
            }
        }

        return true;
    }

    public function enabled(): bool
    {
        if (! ($this->config['enabled'] ?? false) || ! $this->transport->configured()) {
            return false;
        }

        $allowed = (array) ($this->config['environments'] ?? []);

        return $allowed === [] || in_array((string) $this->config['env'], $allowed, true);
    }

    private function dispatch(ErrorEvent $event): bool
    {
        $fingerprint = $event->fingerprint();

        // Jarvis lentasi Telegram bo'g'ishidan OLDIN: soatlik chegara
        // to'lganda ham yangi xato kuzatuvchiga yetib borsin.
        $this->feed?->write($event, $fingerprint);

        $decision = $this->throttle->decide($fingerprint);

        if ($decision['capNotice']) {
            $this->transport->send(
                '⚠️ <b>'.$event->project.'</b> — soatlik chegaraga yetildi, '
                .'qolgan xatolar shu soatda yuborilmaydi.'
            );

            return false;
        }

        if (! $decision['send']) {
            return false;
        }

        return $this->transport->send(EventFormatter::telegram(
            $event,
            $fingerprint,
            $decision['repeated'],
            $decision['firstSeen'],
        ));
    }

    /**
     * So'rov konteksti — xato qaysi chaqiruvda chiqqanini ko'rsatadi.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function context(array $context): array
    {
        $context['breadcrumbs'] ??= $this->crumbs->lines();
        $context['kind'] ??= app()->runningInConsole() ? 'console' : 'http';

        if (! app()->runningInConsole() && ! isset($context['request'])) {
            try {
                /** @var Request $request */
                $request = request();
                $context['request'] = $request->method().' '.$request->path();
                $context['user'] ??= optional($request->user())->getAuthIdentifier();
            } catch (Throwable) {
                // So'rov konteksti yo'q (masalan queue) — muammo emas.
            }
        }

        return $context;
    }

    /**
     * Reliz — xato qaysi kod holatida chiqqani.
     *
     * Deploy skripti `.env` ga yozadi; bo'lmasa `VERSION` faylidan
     * o'qiladi, u ham bo'lmasa reliz ko'rsatilmaydi.
     */
    private function release(): ?string
    {
        if (! empty($this->config['release'])) {
            return (string) $this->config['release'];
        }

        try {
            $file = base_path('VERSION');

            if (is_file($file)) {
                $value = trim((string) file_get_contents($file));

                return $value !== '' ? mb_substr($value, 0, 40) : null;
            }
        } catch (Throwable) {
            // Reliz nomalum bo'lsa ham hisobot yuborilishi kerak.
        }

        return null;
    }
}
