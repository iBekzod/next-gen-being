<?php

namespace App\Services\Telemetry;

/**
 * Hodisani Telegram xabariga aylantiradi — Sentry'dagi issue ko'rinishida.
 *
 * Tartib ataylab shunday: birinchi qatorlarda "qayerda va nima yiqildi"
 * (telefon bildirishnomasida faqat shu ko'rinadi), keyin kontekst, eng
 * oxirida stack — u eng uzun va eng kam o'qiladigan qism.
 */
final class EventFormatter
{
    /** Telegram chegarasi 4096 — zaxira qoldiramiz. */
    private const MAX_LEN = 3900;

    public static function telegram(
        ErrorEvent $event,
        string $fingerprint,
        int $repeated = 0,
        ?string $firstSeen = null,
    ): string {
        $L = [];

        $tags = '#'.preg_replace('/[^A-Za-z0-9_]/', '', $event->project).' #'.$event->kind;
        $L[] = '🔴 <b>'.self::esc($event->project).'</b> · '.self::esc($event->env).'   '.$tags;
        $L[] = '<b>'.self::esc($event->type).'</b>: '.self::esc(Redactor::cap($event->message, 400));

        if ($event->culprit !== null) {
            $L[] = '   ↳ <code>'.self::esc($event->culprit).'</code>';
        }

        $L[] = '';

        if ($repeated > 0 || $firstSeen !== null) {
            $parts = [];
            if ($repeated > 0) {
                $parts[] = $repeated.' marta (oldingi oynada)';
            }
            if ($firstSeen !== null) {
                $parts[] = 'birinchi: '.$firstSeen;
            }
            $L[] = '<b>Takror:</b> '.self::esc(implode(' · ', $parts));
        }

        $meta = array_filter([$event->release, $event->runtime, $event->server]);
        if ($meta !== []) {
            $L[] = '<b>Reliz:</b> '.self::esc(implode(' · ', $meta));
        }

        if ($event->user !== null) {
            $L[] = '<b>User:</b> <code>'.self::esc($event->user).'</code>';
        }

        if ($event->request !== null) {
            $line = '<b>So\'rov:</b> <code>'.self::esc(Redactor::cap($event->request, 300)).'</code>';
            if ($event->statusCode !== null) {
                $line .= ' → '.$event->statusCode;
            }
            $L[] = $line;
        } elseif ($event->statusCode !== null) {
            $L[] = '<b>Status:</b> '.$event->statusCode;
        }

        foreach ($event->extra as $key => $value) {
            $L[] = '<b>'.self::esc($key).':</b> <pre>'.self::esc($value).'</pre>';
        }

        if ($event->breadcrumbs !== []) {
            $L[] = '';
            $L[] = '<b>Oxirgi qadamlar:</b>';
            $L[] = '<pre>'.self::esc(implode("\n", array_slice($event->breadcrumbs, -15))).'</pre>';
        }

        if ($event->stack !== []) {
            $L[] = '';
            $L[] = '<b>Stack:</b>';
            $L[] = '<pre>'.self::esc(Redactor::cap(implode("\n", $event->stack), 1200)).'</pre>';
        }

        $footer = "\n\n<code>#".self::esc($fingerprint).'</code>';

        return self::fit(implode("\n", $L), $footer);
    }

    /**
     * Xabarni chegaraga sig'diradi.
     *
     * Oddiy `substr` yaramaydi: kesilgan joyda yarim qolgan `<pre>` yoki
     * ochiq qolgan teg bo'lsa, Telegram butun xabarni rad etadi
     * ("can't parse entities") — ya'ni uzun xato umuman kelmay qolardi.
     * Barmoq izi esa har doim oxirida turishi kerak, shuning uchun u
     * kesishdan keyin qo'shiladi.
     */
    private static function fit(string $body, string $footer): string
    {
        $limit = self::MAX_LEN - mb_strlen($footer);

        if (mb_strlen($body) <= $limit) {
            return $body.$footer;
        }

        $cut = mb_substr($body, 0, $limit - 20);

        // Yarim qolgan tegni olib tashlaymiz.
        $lt = mb_strrpos($cut, '<');
        $gt = mb_strrpos($cut, '>');
        if ($lt !== false && ($gt === false || $lt > $gt)) {
            $cut = mb_substr($cut, 0, $lt);
        }

        // Ochiq qolgan teglarni yopamiz.
        foreach (['pre', 'code', 'b'] as $tag) {
            $open = substr_count($cut, "<{$tag}>") - substr_count($cut, "</{$tag}>");
            $cut .= str_repeat("</{$tag}>", max(0, $open));
        }

        return $cut."\n…(qisqartirildi)".$footer;
    }

    private static function esc(string $value): string
    {
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $value);
    }
}
