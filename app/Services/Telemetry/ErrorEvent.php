<?php

namespace App\Services\Telemetry;

use Throwable;

/**
 * Bitta xato hodisasi — transportdan mustaqil ko'rinishda.
 *
 * Serverdagi istisno ham, mobil ilovadan kelgan hisobot ham shu shaklga
 * keltiriladi, shundan keyin formatlash va bo'g'ish ikkalasi uchun bir xil
 * ishlaydi.
 */
final class ErrorEvent
{
    /**
     * @param  list<string>  $stack
     * @param  list<string>  $breadcrumbs
     * @param  array<string, string>  $extra
     */
    public function __construct(
        public readonly string $project,
        public readonly string $env,
        public readonly string $kind,
        public readonly string $type,
        public readonly string $message,
        public readonly ?string $culprit = null,
        public readonly array $stack = [],
        public readonly ?string $release = null,
        public readonly ?string $runtime = null,
        public readonly ?string $server = null,
        public readonly ?string $user = null,
        public readonly ?string $request = null,
        public readonly ?int $statusCode = null,
        public readonly array $breadcrumbs = [],
        public readonly array $extra = [],
    ) {}

    /**
     * Serverdagi istisnodan.
     *
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $config
     */
    public static function fromThrowable(Throwable $e, array $context, array $config): self
    {
        $frames = self::frames($e);

        return new self(
            project: (string) $config['project'],
            env: (string) $config['env'],
            kind: (string) ($context['kind'] ?? 'server'),
            type: class_basename($e),
            message: Redactor::text($e->getMessage() !== '' ? $e->getMessage() : class_basename($e)),
            culprit: $frames['culprit'],
            stack: $frames['lines'],
            release: $config['release'] ?? null,
            runtime: 'PHP '.PHP_VERSION,
            server: gethostname() ?: null,
            user: isset($context['user']) ? (string) $context['user'] : null,
            request: isset($context['request']) ? Redactor::text((string) $context['request']) : null,
            statusCode: isset($context['status']) ? (int) $context['status'] : null,
            breadcrumbs: $context['breadcrumbs'] ?? [],
            extra: self::stringifyExtra($context['extra'] ?? []),
        );
    }

    /**
     * Mobil ilovadan kelgan hisobotdan.
     *
     * Ilova tokenni olib yurmaydi — u hisobotni shu backendga beradi, backend
     * botga uzatadi.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $config
     */
    public static function fromClient(array $payload, array $config): self
    {
        $stackText = isset($payload['stack']) ? Redactor::text((string) $payload['stack']) : '';
        $stack = array_values(array_filter(array_map('trim', explode("\n", $stackText))));

        $app = array_filter([
            isset($payload['appVersion']) ? 'v'.$payload['appVersion'] : null,
            $payload['platform'] ?? null,
            $payload['osVersion'] ?? null,
        ]);

        $message = Redactor::text((string) ($payload['error'] ?? 'Noma\'lum xato'));

        return new self(
            project: (string) $config['project'],
            env: (string) ($payload['env'] ?? $config['env']),
            kind: 'app',
            type: self::clientType($message),
            message: $message,
            culprit: self::clientCulprit($stack) ?? ($payload['screen'] ?? null),
            stack: array_slice($stack, 0, 12),
            release: $payload['appVersion'] ?? $config['release'] ?? null,
            runtime: $app === [] ? null : implode(' · ', $app),
            server: null,
            user: isset($payload['userId']) ? (string) $payload['userId'] : null,
            request: isset($payload['request']) ? Redactor::text((string) $payload['request']) : null,
            statusCode: isset($payload['statusCode']) ? (int) $payload['statusCode'] : null,
            breadcrumbs: array_map(
                static fn ($c): string => Redactor::cap(Redactor::text((string) $c), 200),
                array_slice((array) ($payload['breadcrumbs'] ?? []), -15)
            ),
            extra: self::stringifyExtra(array_filter([
                'Ekran' => $payload['screen'] ?? null,
                'Javob' => isset($payload['response']) ? Redactor::text((string) $payload['response']) : null,
                'Ma\'lumot' => isset($payload['data']) ? Redactor::text((string) $payload['data']) : null,
            ])),
        );
    }

    /**
     * Barmoq izi — bir xil xatoni tanish uchun.
     *
     * Xato matnida o'zgaruvchan qismlar bo'ladi (id lar, vaqtlar, yo'llar),
     * shuning uchun ular normallashtiriladi: aks holda har bir id o'zgarganda
     * "yangi" xato bo'lib ko'rinardi va bo'g'ish umuman ishlamasdi.
     */
    public function fingerprint(): string
    {
        $norm = static fn (string $s): string => mb_substr(preg_replace(
            ['/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', '/\d+/'],
            ['<id>', 'N'],
            $s
        ) ?? $s, 0, 300);

        $base = implode('|', [
            $this->kind,
            $this->type,
            $norm($this->message),
            $norm($this->culprit ?? ''),
        ]);

        return substr(base_convert(substr(md5($base), 0, 12), 16, 36), 0, 8);
    }

    /**
     * Stack'ni ilova kadrlariga qarab ajratadi.
     *
     * @return array{culprit: ?string, lines: list<string>}
     */
    private static function frames(Throwable $e): array
    {
        $base = self::basePath();
        $rel = static function (?string $file) use ($base): ?string {
            if ($file === null || $file === '') {
                return null;
            }
            $file = str_replace('\\', '/', $file);

            return $base !== null && str_starts_with($file, $base)
                ? ltrim(substr($file, strlen($base)), '/')
                : $file;
        };

        $isApp = static fn (?string $file): bool => $file !== null
            && ! str_contains($file, 'vendor/')
            && ! str_contains($file, '/vendor\\');

        $culprit = null;
        $lines = [];

        $first = $rel($e->getFile());
        if ($isApp($first)) {
            $culprit = $first.':'.$e->getLine();
        }
        if ($first !== null) {
            $lines[] = '#0 '.$first.':'.$e->getLine();
        }

        foreach (array_slice($e->getTrace(), 0, 20) as $i => $frame) {
            $file = $rel($frame['file'] ?? null);
            if ($file === null) {
                continue;
            }

            $call = ($frame['class'] ?? '') !== ''
                ? class_basename($frame['class']).($frame['type'] ?? '::').($frame['function'] ?? '')
                : ($frame['function'] ?? '');

            $line = '#'.($i + 1).' '.$file.':'.($frame['line'] ?? '?').' '.$call.'()';

            if ($culprit === null && $isApp($file)) {
                $culprit = $file.':'.($frame['line'] ?? '?');
            }

            // Freymvork ichidagi o'nlab kadr foydasiz — ilova kadrlari va
            // ularning eng yaqin qo'shnilari yetarli.
            if ($isApp($file) || count($lines) < 6) {
                $lines[] = $line;
            }

            if (count($lines) >= 12) {
                break;
            }
        }

        return ['culprit' => $culprit, 'lines' => $lines];
    }

    private static function basePath(): ?string
    {
        if (! function_exists('base_path')) {
            return null;
        }

        try {
            return rtrim(str_replace('\\', '/', base_path()), '/');
        } catch (Throwable) {
            return null;
        }
    }

    /** Dart xatosining turini matn boshidan ajratadi: "Xxx: tafsilot". */
    private static function clientType(string $message): string
    {
        if (preg_match('/^([A-Z][A-Za-z0-9_]{2,40})(Exception|Error)?\s*:/', $message, $m) === 1) {
            return $m[1].($m[2] ?? '');
        }

        return 'AppError';
    }

    /**
     * Ilova kodidagi birinchi kadr — Flutter/Dart kutubxonalari o'tkazib
     * yuboriladi, aks holda har doim `framework.dart` ko'rinardi.
     *
     * @param  list<string>  $stack
     */
    private static function clientCulprit(array $stack): ?string
    {
        foreach ($stack as $line) {
            if (preg_match('#\((package:[^/]+/[^)]+)\)#', $line, $m) === 1
                && ! str_contains($m[1], 'package:flutter/')) {
                return $m[1];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, string>
     */
    private static function stringifyExtra(array $extra): array
    {
        $out = [];

        foreach ($extra as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $text = is_scalar($value) ? (string) $value : (json_encode($value) ?: '');
            $out[(string) $key] = Redactor::cap(Redactor::text($text), 700);
        }

        return $out;
    }
}
