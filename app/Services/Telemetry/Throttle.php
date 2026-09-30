<?php

namespace App\Services\Telemetry;

use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Xabarlar oqimini bo'g'adi.
 *
 * Ikki qavat, chunki ikki xil to'foni bor:
 *
 *  1. **Bitta xato takrorlanadi** (masalan har so'rovda) — barmoq izi
 *     bo'yicha oyna: 10 daqiqada bir marta yuboriladi, qolgani sanaladi va
 *     keyingi xabarda "N marta" bo'lib chiqadi.
 *  2. **Ko'p xil xato bir vaqtda** (deploy buzildi) — soatlik chegara:
 *     40 xabardan keyin bitta ogohlantirish va jimlik. Chat o'qib
 *     bo'ladigan holda qoladi.
 */
final class Throttle
{
    private const PREFIX = 'errrep:';

    public function __construct(
        private readonly Cache $cache,
        private readonly int $dedupeTtl = 600,
        private readonly int $hourlyCap = 40,
    ) {}

    /**
     * @return array{send: bool, repeated: int, firstSeen: ?string, capNotice: bool}
     */
    public function decide(string $fingerprint): array
    {
        $skip = ['send' => false, 'repeated' => 0, 'firstSeen' => null, 'capNotice' => false];

        $windowKey = self::PREFIX.'fp:'.$fingerprint;
        $firstKey = self::PREFIX.'first:'.$fingerprint;
        $prevKey = self::PREFIX.'prev:'.$fingerprint;

        // `add` faqat kalit yo'q bo'lganda yozadi — ya'ni oynani kim
        // ochganini atomik aniqlaydi.
        $opensWindow = $this->cache->add($windowKey, 0, $this->dedupeTtl);
        $count = (int) $this->cache->increment($windowKey);

        if (! $opensWindow) {
            // Takror: yuborilmaydi, lekin keyingi xabarda ko'rinishi uchun
            // sanog'i saqlanadi.
            $this->cache->put($prevKey, $count, 86400);

            return $skip;
        }

        $now = date('H:i');
        $this->cache->add($firstKey, $now, 86400);
        $firstSeen = (string) $this->cache->get($firstKey, $now);
        $repeated = (int) $this->cache->pull($prevKey, 0);

        $capKey = self::PREFIX.'cap:'.floor(time() / 3600);
        $this->cache->add($capKey, 0, 3600);
        $used = (int) $this->cache->increment($capKey);

        if ($used > $this->hourlyCap) {
            // Chegaradan keyin faqat BITTA ogohlantirish — aks holda
            // ogohlantirishning o'zi to'fonga aylanardi.
            return $used === $this->hourlyCap + 1
                ? ['send' => false, 'repeated' => 0, 'firstSeen' => null, 'capNotice' => true]
                : $skip;
        }

        return [
            'send' => true,
            'repeated' => $repeated,
            'firstSeen' => $firstSeen === $now ? null : $firstSeen,
            'capNotice' => false,
        ];
    }
}
