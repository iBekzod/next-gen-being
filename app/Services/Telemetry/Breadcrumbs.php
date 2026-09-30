<?php

namespace App\Services\Telemetry;

/**
 * Xatodan oldin nima bo'lganini eslab turadigan halqali bufer.
 *
 * Xatoning o'zi ko'pincha yetarli emas: "null'da xususiyat o'qildi" degani
 * nima uchun null bo'lganini aytmaydi. Oxirgi so'rovlar, HTTP chaqiruvlari
 * va job'lar shu savolga javob beradi.
 *
 * Hammasi xotirada: so'rov tugashi bilan yo'qoladi, hech qayerga saqlanmaydi.
 */
final class Breadcrumbs
{
    /**
     * 40 ta yetarli: undan ortig'i Telegram xabarini to'ldiradi, foydalisi
     * esa baribir oxirgi bir nechtasi.
     */
    private const MAX = 40;

    /** @var list<array{time: string, kind: string, message: string}> */
    private array $items = [];

    public function add(string $kind, string $message): void
    {
        $this->items[] = [
            'time' => date('H:i:s'),
            'kind' => $kind,
            'message' => Redactor::cap(Redactor::text($message), 200),
        ];

        if (count($this->items) > self::MAX) {
            array_shift($this->items);
        }
    }

    /** @return list<string> */
    public function lines(int $last = 15): array
    {
        $slice = array_slice($this->items, -$last);

        return array_map(
            static fn (array $c): string => "{$c['time']}  {$c['kind']}  {$c['message']}",
            $slice
        );
    }

    public function clear(): void
    {
        $this->items = [];
    }
}
