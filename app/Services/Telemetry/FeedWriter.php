<?php

namespace App\Services\Telemetry;

use Illuminate\Contracts\Cache\Repository as Cache;
use Throwable;

/**
 * Har xato hodisasini JSON qatori sifatida faylga yozadi — Jarvis uchun.
 *
 * ## Nega Telegram yetmaydi
 *
 * Bot boshqa botning xabarini o'qiy olmaydi, `getUpdates` esa faqat botga
 * KELGAN xabarlarni beradi. Ya'ni forum-guruhdagi hisobotlarni hech bir
 * dastur qayta o'qiy olmaydi. Jarvis (egasining kompyuteridagi
 * `errors.js` kuzatuvchisi) shu faylni `ssh ... tail` bilan oladi,
 * barmoq izi bo'yicha saralaydi va yangi xatoga darhol tahlil boshlaydi.
 *
 * Format — umumiy (MyStatus Redis ro'yxati ham shu maydonlar bilan):
 * `{v, id, ts, project, env, kind, type, fingerprint, culprit, message,
 *   stack, release, request, status, runtime, server}`.
 *
 * Bir barmoq izi daqiqasiga ko'pi bilan bir marta yoziladi: tsiklda
 * aylanayotgan xato faylni shishirmasin. Fayl 2 MB dan oshsa `.1` ga
 * suriladi. Hech qachon istisno tashlamaydi.
 */
final class FeedWriter
{
    private const MAX_BYTES = 2_000_000;

    public function __construct(
        private readonly string $path,
        private readonly ?Cache $cache = null,
    ) {}

    public function write(ErrorEvent $event, string $fingerprint): void
    {
        try {
            if ($this->cache !== null
                && ! $this->cache->add('errfeed:'.$fingerprint, 1, 60)) {
                return;
            }

            $row = [
                'v' => 1,
                'id' => date('YmdHis').'-'.$fingerprint.'-'.substr(bin2hex(random_bytes(3)), 0, 6),
                'ts' => date(DATE_ATOM),
                'project' => $event->project,
                'env' => $event->env,
                'kind' => $event->kind,
                'type' => $event->type,
                'fingerprint' => $fingerprint,
                'culprit' => $event->culprit,
                'message' => mb_substr($event->message, 0, 1000),
                'stack' => mb_substr(implode("\n", $event->stack), 0, 2500),
                'release' => $event->release,
                'request' => $event->request,
                'status' => $event->statusCode,
                'runtime' => $event->runtime,
                'server' => $event->server,
            ];

            $dir = dirname($this->path);
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            if (@filesize($this->path) > self::MAX_BYTES) {
                @rename($this->path, $this->path.'.1');
            }

            @file_put_contents(
                $this->path,
                json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)."\n",
                FILE_APPEND | LOCK_EX,
            );
        } catch (Throwable) {
            // Lenta yozilmagani Telegram hisobotini ham, ilovani ham buzmaydi.
        }
    }
}
